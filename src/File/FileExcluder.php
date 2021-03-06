<?php declare(strict_types = 1);

namespace PHPStan\File;

class FileExcluder
{

	/**
	 * Directories to exclude from analysing
	 *
	 * @var string[]
	 */
	private $analyseExcludes;

	public function __construct(
		FileHelper $fileHelper,
		array $analyseExcludes
	)
	{
		$this->analyseExcludes = array_map(function (string $exclude) use ($fileHelper): string {
			$normalized = $fileHelper->normalizePath($exclude);

			if ($this->isFnmatchPattern($normalized)) {
				return $normalized;
			}

			return $fileHelper->absolutizePath($normalized);
		}, $analyseExcludes);
	}

	public function isExcludedFromAnalysing(string $file): bool
	{
		foreach ($this->analyseExcludes as $exclude) {
			if (strpos($file, $exclude) === 0) {
				return true;
			}

			if ($this->isFnmatchPattern($exclude) && fnmatch($exclude, $file, DIRECTORY_SEPARATOR === '\\' ? FNM_NOESCAPE : 0)) {
				return true;
			}
		}

		return false;
	}

	private function isFnmatchPattern(string $path): bool
	{
		return preg_match('~[*?[\]]~', $path) > 0;
	}

}
