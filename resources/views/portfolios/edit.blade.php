<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Portfolio</title>
</head>
<body>

    <h1>Edit Portfolio</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('portfolio.update', $portfolio->id) }}" method="POST">

        @csrf
        @method('PUT')

        <div>
            <label>Judul Portfolio</label>
            <br>
            <input
                type="text"
                name="title"
                value="{{ old('title', $portfolio->title) }}"
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
            >{{ old('description', $portfolio->description) }}</textarea>
        </div>

        <br>

        <button type="submit">
            Update
        </button>

        <a href="{{ route('portfolio.index') }}">
            Kembali
        </a>

    </form>

</body>
</html>