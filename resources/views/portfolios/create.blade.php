<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Portfolio</title>
</head>
<body>

    <h1>Tambah Portfolio</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('portfolio.store') }}" method="POST">

        @csrf

        <div>
            <label>Judul Portfolio</label>
            <br>
            <input
                type="text"
                name="title"
                value="{{ old('title') }}"
                required
            >
        </div>

        <br>

        <div>
            <label>Deskripsi</label>
            <br>
            <textarea
                name="description"
                rows="5"
            >{{ old('description') }}</textarea>
        </div>

        <br>

        <button type="submit">
            Simpan
        </button>

        <a href="{{ route('portfolio.index') }}">
            Kembali
        </a>

    </form>

</body>
</html>