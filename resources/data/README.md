The domicile picker ships with a local snapshot of Indonesia's 38 provinces and
514 kabupaten/kota. Names, codes and administrative reference coordinates come
from [cahyadsn/wilayah](https://github.com/cahyadsn/wilayah), redistributed through
the [emsifa static API](https://github.com/emsifa/api-wilayah-indonesia).

The source is based on Kepmendagri No. 300.2.2-2430 Tahun 2025 and uses the MIT
license reproduced in `indonesia-domicile-LICENSE.txt`. The JSON metadata records
the retrieval date and upstream generation date. Refresh the snapshot with:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/update-domicile-data.ps1
```

The application makes no runtime requests to that API. PHP renders the full list
as a native select, so selecting a domicile also works without JavaScript or GPS.
JavaScript adds a province filter and an optional browser GPS suggestion.

GPS suggestions compare the current position with administrative reference
points in the snapshot. These points are not polygons or a reverse geocoder and
can suggest a neighboring region. Five approximate candidates are shown and the
user must select and explicitly confirm one. Only the chosen region's text is
submitted; the current coordinates are never sent to the application server or
stored in a draft or portfolio. Browser geolocation requires a secure context
(HTTPS or localhost) and user permission, per the
[Geolocation API documentation](https://developer.mozilla.org/en-US/docs/Web/API/Geolocation/getCurrentPosition).

Existing free-text domicile values remain selectable until the user replaces or
clears them. The form continues to submit `personal[location]` for compatibility
with existing drafts and published portfolios.
