<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\File;

class PortfolioPdf
{
    public function render(array $draft): string
    {
        $cachePath = storage_path('framework/cache/dompdf');
        File::ensureDirectoryExists($cachePath);

        $options = new Options([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => false,
            'isPhpEnabled' => false,
            'isJavascriptEnabled' => false,
            'tempDir' => $cachePath,
            'fontCache' => $cachePath,
            'chroot' => [resource_path('views/builder'), base_path('vendor/dompdf/dompdf/lib/fonts')],
        ]);
        $pdf = new Dompdf($options);
        $pdf->setPaper('A4', 'portrait');
        $pdf->loadHtml($this->html($draft), 'UTF-8');
        $pdf->render();
        $pdf->getCanvas()->page_text(44, 809, 'Portofolio  |  {PAGE_NUM} / {PAGE_COUNT}', null, 8, [0.35, 0.39, 0.45]);

        return $pdf->output();
    }

    public function html(array $draft): string
    {
        return view('builder.pdf', [
            'draft' => $draft,
            'photo' => $this->photo($draft['personal']['photo'] ?? null),
            'safeUrl' => static function (?string $url): ?string {
                return $url && filter_var($url, FILTER_VALIDATE_URL)
                    && in_array(strtolower(parse_url($url, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true)
                    ? $url : null;
            },
        ])->render();
    }

    private function photo(?string $photo): ?string
    {
        if (! $photo || strlen($photo) > 3 * 1024 * 1024
            || ! preg_match('/\Adata:image\/(png|jpeg|webp);base64,([A-Za-z0-9+\/=]+)\z/', $photo, $matches)) {
            return null;
        }

        $bytes = base64_decode($matches[2], true);
        $size = $bytes !== false ? @getimagesizefromstring($bytes) : false;
        if (! $size || ! in_array($size[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_WEBP], true)
            || $size[0] * $size[1] > 12000000) {
            return null;
        }

        if ($size[2] === IMAGETYPE_WEBP) {
            if (! function_exists('imagecreatefromstring')) {
                return null;
            }
            $image = @imagecreatefromstring($bytes);
            if (! $image) {
                return null;
            }
            ob_start();
            imagepng($image);
            $bytes = ob_get_clean();
            imagedestroy($image);

            return 'data:image/png;base64,'.base64_encode($bytes);
        }

        return 'data:'.$size['mime'].';base64,'.base64_encode($bytes);
    }
}
