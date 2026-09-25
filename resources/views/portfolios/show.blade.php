<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Portfolio</title>
</head>
<body>

    <h1>Detail Portfolio</h1>

    <h2>{{ $portfolio->title }}</h2>

    <p>{{ $portfolio->description }}</p>

    <br>

    <a href="{{ route('portfolio.edit', $portfolio->id) }}">
        Edit
    </a>

    |

    <a href="{{ route('portfolio.index') }}">
        Kembali
    </a>

</body>
</html>