param(
    [Parameter(Mandatory = $true)]
    [string] $Source,
    [Parameter(Mandatory = $true)]
    [string] $Destination
)

$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Drawing

New-Item -ItemType Directory -Force -Path $Destination | Out-Null

$rows = foreach ($file in Get-ChildItem -LiteralPath $Source -File | Sort-Object Name) {
    try {
        $image = [System.Drawing.Image]::FromFile($file.FullName)
        [pscustomobject]@{
            Name        = $file.Name
            Width       = $image.Width
            Height      = $image.Height
            Orientation = if ($image.Width -gt $image.Height) { 'landscape' } elseif ($image.Width -lt $image.Height) { 'portrait' } else { 'square' }
            Megabytes   = [math]::Round($file.Length / 1MB, 2)
        }
        $image.Dispose()
    } catch {
        [pscustomobject]@{
            Name        = $file.Name
            Width       = 0
            Height      = 0
            Orientation = 'unreadable'
            Megabytes   = [math]::Round($file.Length / 1MB, 2)
        }
    }
}

$rows | Export-Csv -LiteralPath (Join-Path $Destination 'inventory.csv') -NoTypeInformation -Encoding UTF8
$rows | Format-Table -AutoSize
