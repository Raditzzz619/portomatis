<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio</title>
</head>
<body>

    <h1>Daftar Portfolio</h1>

    @if (session('success'))
        <p style="color: green;">
            {{ session('success') }}
        </p>
    @endif

    <a href="{{ route('portfolio.create') }}">+ Tambah Portfolio</a>

    <hr>

    @forelse ($portfolios as $portfolio)

        <h2>{{ $portfolio->title }}</h2>

        <p>{{ $portfolio->description }}</p>

        <a href="{{ route('portfolio.show', $portfolio->id) }}">
            Detail
        </a>

        |

        <a href="{{ route('portfolio.edit', $portfolio->id) }}">
            Edit
        </a>

        |

        <form action="{{ route('portfolio.destroy', $portfolio->id) }}"
              method="POST"
              style="display: inline;">

            @csrf
            @method('DELETE')

            <button type="submit"
                    onclick="return confirm('Yakin ingin menghapus portfolio ini?')">
                Hapus
            </button>

        </form>

        <hr>

    @empty

        <p>Belum ada portfolio.</p>

    @endforelse

</body>
</html>