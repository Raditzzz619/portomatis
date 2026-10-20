$ErrorActionPreference = 'Stop'

# The API redistributes cahyadsn/wilayah's MIT-licensed administrative facts.
# Only names, codes and reference coordinates are retained in the local snapshot.
$baseUrl = 'https://www.emsifa.com/api-wilayah-indonesia/v2'
$provinceResponse = Invoke-RestMethod -Uri "$baseUrl/provinces.json" -TimeoutSec 30
$provinces = @()
foreach ($province in $provinceResponse.data) {
    if ($province.id -notmatch '^\d{2}$' -or [string]::IsNullOrWhiteSpace($province.name)) {
        throw 'Invalid province code or name. The existing local snapshot has not been changed.'
    }
    $regencyResponse = Invoke-RestMethod -Uri "$baseUrl/regencies/$($province.id).json" -TimeoutSec 30
    $regencies = @($regencyResponse.data | ForEach-Object {
        if ($_.id -notmatch ('^' + [regex]::Escape($province.id) + '\.\d{2}$') -or [string]::IsNullOrWhiteSpace($_.name)) {
            throw 'Invalid regency code or name. The existing local snapshot has not been changed.'
        }
        $referenceLatitude = $null
        $referenceLongitude = $null
        if ($null -ne $_.lat -and $null -ne $_.lng -and $_.lat -ge -90 -and $_.lat -le 90 -and $_.lng -ge -180 -and $_.lng -le 180) {
            $referenceLatitude = [Math]::Round($_.lat, 5)
            $referenceLongitude = [Math]::Round($_.lng, 5)
        }
        [ordered]@{ id = [string]$_.id; name = $_.name; lat = $referenceLatitude; lng = $referenceLongitude }
    })
    $provinces += [ordered]@{ id = [string]$province.id; name = $province.name; regencies = $regencies }
}

$allRegencies = @($provinces | ForEach-Object { $_.regencies })
if ($provinces.Count -ne 38 -or $allRegencies.Count -ne 514 -or @($provinces.id | Select-Object -Unique).Count -ne 38 -or @($allRegencies.id | Select-Object -Unique).Count -ne 514) {
    throw 'Unexpected region counts. Review the upstream dataset before replacing the local snapshot.'
}

$snapshot = [ordered]@{
    meta = [ordered]@{
        source = 'https://github.com/cahyadsn/wilayah'
        distributor = $baseUrl
        license = 'MIT (Copyright (c) 2017-2025 Cahya DSN); see indonesia-domicile-LICENSE.txt'
        administrative_reference = 'Kepmendagri No. 300.2.2-2430 Tahun 2025'
        retrieved_at = (Get-Date).ToString('yyyy-MM-dd')
        upstream_updated_at = $provinceResponse.meta.updated_at
        coordinates = 'Administrative reference points, not boundary polygons; approximate GPS suggestions only.'
    }
    provinces = $provinces
}
$destination = Join-Path $PSScriptRoot '../resources/data/indonesia-domicile.json'
$destinationDirectory = Split-Path -Parent $destination
New-Item -ItemType Directory -Path $destinationDirectory -Force | Out-Null
[System.IO.File]::WriteAllText($destination, ($snapshot | ConvertTo-Json -Depth 8 -Compress), [System.Text.UTF8Encoding]::new($false))
Write-Output "Saved 38 provinces and 514 regencies to $destination"
