[CmdletBinding()]
param(
	[Parameter(Mandatory = $true)]
	[string] $Destination
)

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version 2.0

$repositoryRoot = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$destinationRoot = [System.IO.Path]::GetFullPath($Destination)

if ($destinationRoot -eq $repositoryRoot -or $destinationRoot.StartsWith($repositoryRoot + [System.IO.Path]::DirectorySeparatorChar)) {
	throw 'La destination de staging doit etre situee hors du depot source.'
}

if (Test-Path -LiteralPath $destinationRoot) {
	$existingItems = @(Get-ChildItem -LiteralPath $destinationRoot -Force)
	if ($existingItems.Count -gt 0) {
		throw "La destination doit etre absente ou vide : $destinationRoot"
	}
} else {
	New-Item -ItemType Directory -Path $destinationRoot | Out-Null
}

$moduleNames = @(
	'association-adhesions',
	'association-communication',
	'association-compta',
	'association-dons',
	'association-evenements',
	'association-groupes',
	'association-paiements',
	'association-prets',
	'association-ventes'
)
$pluginNames = @('association') + $moduleNames

$trackedFiles = @(& git -C $repositoryRoot ls-files)
if ($LASTEXITCODE -ne 0 -or $trackedFiles.Count -eq 0) {
	throw 'Impossible de lire la liste des fichiers versionnes avec git ls-files.'
}

function Copy-TrackedFile {
	param(
		[string] $RepositoryRelativePath,
		[string] $PluginRelativePath,
		[string] $PluginTarget
	)

	$source = Join-Path $repositoryRoot ($RepositoryRelativePath -replace '/', [System.IO.Path]::DirectorySeparatorChar)
	$target = Join-Path $PluginTarget ($PluginRelativePath -replace '/', [System.IO.Path]::DirectorySeparatorChar)
	$targetParent = Split-Path -Parent $target
	if (!(Test-Path -LiteralPath $targetParent)) {
		New-Item -ItemType Directory -Path $targetParent -Force | Out-Null
	}
	Copy-Item -LiteralPath $source -Destination $target
}

foreach ($pluginName in $pluginNames) {
	$pluginTarget = Join-Path $destinationRoot $pluginName
	New-Item -ItemType Directory -Path $pluginTarget | Out-Null

	if ($pluginName -eq 'association') {
		$pluginFiles = @($trackedFiles | Where-Object {
			$_ -notmatch '^plugins/' -and
			$_ -notmatch '^tools/' -and
			$_ -notmatch '^\.github/' -and
			$_ -ne 'AGENTS.md'
		})
		foreach ($file in $pluginFiles) {
			Copy-TrackedFile -RepositoryRelativePath $file -PluginRelativePath $file -PluginTarget $pluginTarget
		}
	} else {
		$modulePrefix = 'plugins/' + $pluginName + '/'
		$pluginFiles = @($trackedFiles | Where-Object { $_.StartsWith($modulePrefix, [System.StringComparison]::Ordinal) })
		foreach ($file in $pluginFiles) {
			$relative = $file.Substring($modulePrefix.Length)
			Copy-TrackedFile -RepositoryRelativePath $file -PluginRelativePath $relative -PluginTarget $pluginTarget
		}
	}

	if (!(Test-Path -LiteralPath (Join-Path $pluginTarget 'paquet.xml'))) {
		throw "paquet.xml absent du staging de $pluginName"
	}
}

function Get-PluginDigest {
	param([string] $PluginPath)

	$lines = New-Object System.Collections.Generic.List[string]
	$files = @(Get-ChildItem -LiteralPath $PluginPath -File -Recurse | Sort-Object FullName)
	foreach ($file in $files) {
		$relative = $file.FullName.Substring($PluginPath.Length).TrimStart('\', '/') -replace '\\', '/'
		$fileStream = [System.IO.File]::OpenRead($file.FullName)
		$fileSha = [System.Security.Cryptography.SHA256]::Create()
		try {
			$fileHashBytes = $fileSha.ComputeHash($fileStream)
			$fileHash = ([System.BitConverter]::ToString($fileHashBytes) -replace '-', '').ToLowerInvariant()
		} finally {
			$fileSha.Dispose()
			$fileStream.Dispose()
		}
		$lines.Add($relative + '|' + $fileHash)
	}
	$payload = [string]::Join("`n", $lines)
	$sha = [System.Security.Cryptography.SHA256]::Create()
	try {
		$digestBytes = $sha.ComputeHash([System.Text.Encoding]::UTF8.GetBytes($payload))
	} finally {
		$sha.Dispose()
	}
	return [pscustomobject]@{
		sha256 = ([System.BitConverter]::ToString($digestBytes) -replace '-', '').ToLowerInvariant()
		files = $files.Count
	}
}

$manifestPlugins = @()
foreach ($pluginName in $pluginNames) {
	$pluginPath = Join-Path $destinationRoot $pluginName
	[xml] $package = Get-Content -LiteralPath (Join-Path $pluginPath 'paquet.xml') -Raw
	$digest = Get-PluginDigest -PluginPath $pluginPath
	$manifestPlugins += [ordered]@{
		prefix = [string] $package.paquet.prefix
		version = [string] $package.paquet.version
		directory = $pluginName
		files = $digest.files
		sha256 = $digest.sha256
	}
}

$manifest = [ordered]@{
	format = 1
	generated_at_utc = [DateTime]::UtcNow.ToString('o')
	git_commit = (& git -C $repositoryRoot rev-parse HEAD).Trim()
	plugins = $manifestPlugins
}
$manifestPath = Join-Path $destinationRoot 'association-suite-manifest.json'
$manifestJson = $manifest | ConvertTo-Json -Depth 5
$utf8WithoutBom = New-Object System.Text.UTF8Encoding($false)
[System.IO.File]::WriteAllText($manifestPath, $manifestJson, $utf8WithoutBom)

Write-Output $manifestPath
