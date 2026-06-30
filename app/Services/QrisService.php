<?php

namespace App\Services;

/**
 * QrisService – Static-to-Dynamic QRIS Parser
 *
 * Converts a standard Static QRIS string (EMVCo QR Code Specification)
 * into a Dynamic QRIS string by injecting the transaction amount (Tag 54)
 * and recalculating the CRC-16/CCITT-FALSE checksum.
 *
 * This avoids external PJP/payment-gateway API calls and their associated
 * transaction fees, performing the conversion entirely on the backend.
 *
 * ─── EMVCo Tag 54 Structure ───
 * Tag 54 represents "Transaction Amount" in the EMVCo QR specification.
 * Format: "54" + LL + V
 *   - "54"  → Tag ID for Transaction Amount
 *   - LL    → 2-digit length of the value, zero-padded (e.g. "05" for "15000")
 *   - V     → The amount as a string with no decimals (e.g. "15000")
 *
 * ─── CRC-16/CCITT-FALSE ───
 * The last 4 hex characters of any EMVCo QR string are a CRC checksum.
 * The tag is always "63" with length "04", so the string always ends with
 * "6304" followed by 4 uppercase hex digits.
 * Algorithm: CRC-16 with polynomial 0x1021 and initial value 0xFFFF.
 */
class QrisService
{
    /**
     * Convert a Static QRIS string to a Dynamic QRIS string.
     *
     * @param  string     $staticQris  Raw static QRIS string (EMVCo-compliant).
     * @param  int|float  $amount      Transaction amount (e.g. 15000 or 15000.00).
     * @return string     The complete dynamic QRIS string with Tag 54 and new CRC.
     *
     * @throws \InvalidArgumentException  When the input QRIS or amount is invalid.
     */
    public function convertToDynamic(string $staticQris, int|float $amount): string
    {
        // ── 1. Validate inputs ──────────────────────────────────────────
        $staticQris = trim($staticQris);

        if (strlen($staticQris) < 10) {
            throw new \InvalidArgumentException('QRIS string is too short to be valid.');
        }

        if ($amount <= 0) {
            throw new \InvalidArgumentException('Transaction amount must be greater than zero.');
        }

        // ── 2. Strip the existing CRC (last 8 chars: "6304" + 4-hex checksum) ──
        //    Every EMVCo QR string ends with Tag 63 (CRC).
        //    Tag "63", Length "04", Value = 4-char hex → total 8 characters.
        $strippedQris = substr($staticQris, 0, -8);

        // ── 3. Build Tag 54 (Transaction Amount) ────────────────────────
        //    Remove any decimal places – QRIS amounts are whole integers (IDR).
        $amountString = (string) intval($amount);

        //    LL = 2-digit zero-padded length of the amount string.
        //    Example: amount "15000" → length 5 → LL = "05"
        $lengthTag = str_pad(strlen($amountString), 2, '0', STR_PAD_LEFT);

        //    Full Tag 54 = "54" + LL + V
        //    Example: "54" + "05" + "15000" = "540515000"
        $tag54 = '54' . $lengthTag . $amountString;

        // ── 4. Reassemble the QRIS string ───────────────────────────────
        //    Append Tag 54, then the CRC tag initiator "6304".
        //    The CRC calculation includes the "6304" tag header itself.
        $qrisWithoutChecksum = $strippedQris . $tag54 . '6304';

        // ── 5. Calculate CRC-16/CCITT-FALSE ─────────────────────────────
        $crc = $this->calculateCrc16($qrisWithoutChecksum);

        // ── 6. Return the final dynamic QRIS string ────────────────────
        return $qrisWithoutChecksum . $crc;
    }

    /**
     * Calculate the CRC-16/CCITT-FALSE checksum.
     *
     * Algorithm details:
     *   - Polynomial : 0x1021  (x¹⁶ + x¹² + x⁵ + 1)
     *   - Initial    : 0xFFFF
     *   - Reflect In : false
     *   - Reflect Out: false
     *   - XOR Out    : 0x0000
     *
     * The result is a 4-character uppercase hexadecimal string.
     *
     * @param  string  $data  The payload string to checksum.
     * @return string  4-character uppercase hex CRC (e.g. "A1B2").
     */
    public function calculateCrc16(string $data): string
    {
        $crc = 0xFFFF; // Initial value

        for ($i = 0, $len = strlen($data); $i < $len; $i++) {
            // XOR the next byte into the high-order byte of the CRC register.
            $crc ^= (ord($data[$i]) << 8);

            // Process each bit in the byte.
            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    // If MSB is set, shift left and XOR with the polynomial.
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    // Otherwise, just shift left.
                    $crc <<= 1;
                }

                // Mask to 16 bits to prevent overflow in PHP's integer arithmetic.
                $crc &= 0xFFFF;
            }
        }

        // Return as 4-digit uppercase hexadecimal.
        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
