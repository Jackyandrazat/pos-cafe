<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kode Akses Admin</title>
    <style>
        :root {
            color-scheme: light dark;
            --bg: #0f172a;
            --card: #1e293b;
            --text: #f8fafc;
            --muted: #94a3b8;
            --accent: #fbbf24;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top, rgba(251, 191, 36, .25), rgba(15, 23, 42, 1) 60%), var(--bg);
            color: var(--text);
            padding: 1.5rem;
        }

        .card {
            width: min(420px, 100%);
            padding: 2rem;
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(251, 191, 36, 0.2);
            border-radius: 1.5rem;
            box-shadow: 0 20px 80px rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(10px);
        }

        h1 {
            margin: 0 0 0.5rem;
            font-size: 1.75rem;
        }

        p {
            margin: 0 0 1.5rem;
            color: var(--muted);
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        input[type="password"] {
            width: 100%;
            padding: 0.9rem 1rem;
            border-radius: 0.85rem;
            border: 1px solid rgba(148, 163, 184, 0.3);
            background: rgba(15, 23, 42, 0.6);
            color: inherit;
            font-size: 1rem;
        }

        button {
            margin-top: 1.5rem;
            width: 100%;
            padding: 0.95rem 1rem;
            border: none;
            border-radius: 0.85rem;
            font-weight: 600;
            font-size: 1rem;
            color: #0f172a;
            background: var(--accent);
            cursor: pointer;
            transition: transform 120ms ease, box-shadow 120ms ease;
        }

        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 30px rgba(251, 191, 36, 0.25);
        }

        .error {
            margin-top: 0.75rem;
            background: rgba(239, 68, 68, 0.15);
            color: #fecaca;
            border: 1px solid rgba(239, 68, 68, 0.4);
            border-radius: 0.85rem;
            padding: 0.75rem 1rem;
        }
    </style>
</head>

<body>
    <main class="card">
        <form method="POST" action="{{ route('admin.access-code.store') }}">
            @csrf
            <h1>Masukkan Kode Akses</h1>
            <p>Kode ini memastikan hanya tim berwenang yang dapat membuka halaman admin.</p>

            <label for="access_code">Kode Akses</label>
            <input
                id="access_code"
                name="access_code"
                type="password"
                autocomplete="one-time-code"
                required
                placeholder="••••••"
                value="{{ old('access_code') }}"
            >

            @error('access_code')
                <div class="error">{{ $message }}</div>
            @enderror

            <button type="submit">Verifikasi</button>
        </form>
    </main>
</body>

</html>
