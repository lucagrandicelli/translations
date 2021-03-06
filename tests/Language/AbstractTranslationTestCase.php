<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translations\Tests;

use PHPUnit\Framework\TestCase;

/**
 * This abstract test class does the heavy testing of the sufficiency
 * and precision of provided per-locale translations.
 *
 * New tests for new locales should extend this class and marked as "final".
 *
 * @internal
 */
abstract class AbstractTranslationTestCase extends TestCase
{
	/**
	 * Relative path to the main language folder in main repository.
	 *
	 * @var string
	 */
	private const MAIN_LANGUAGE_REPO = '/vendor/codeigniter4/codeigniter4/system/Language/en/';

	/**
	 * Collection of all locale codes mapped from the
	 * individual locale translation test case.
	 *
	 * @var array<string, string>
	 */
	public static $locales = [
		ArabicTranslationTest::class             => 'ar',
		CzechTranslationTest::class              => 'cs',
		GermanTranslationTest::class             => 'de',
		SpanishTranslationTest::class            => 'es',
		FarsiTranslationTest::class              => 'fa',
		FrenchTranslationTest::class             => 'fr',
		IndonesianTranslationTest::class         => 'id',
		ItalianTranslationTest::class            => 'it',
		JapaneseTranslationTest::class           => 'ja',
		KoreanTranslationTest::class             => 'ko',
		LithuanianTranslationTest::class         => 'lt',
		LatvianTranslationTest::class            => 'lv',
		MalayalamTranslationTest::class          => 'ml',
		DutchTranslationTest::class              => 'nl',
		NorwegianTranslationTest::class          => 'no',
		PolishTranslationTest::class             => 'pl',
		PortugueseTranslationTest::class         => 'pt',
		BrazilianTranslationTest::class          => 'pt-BR',
		RussianTranslationTest::class            => 'ru',
		SlovakTranslationTest::class             => 'sk',
		SwedishTranslationTest::class            => 'sv-SE',
		TurkishTranslationTest::class            => 'tr',
		UkrainianTranslationTest::class          => 'uk',
		VietnameseTranslationTest::class         => 'vi',
		SimpleChineseTranslationTest::class      => 'zh-CN',
		TraditionalChineseTranslationTest::class => 'zh-TW',
	];

	//-------------------------------------------------------------------------
	// TESTS
	//-------------------------------------------------------------------------

	/**
	 * This tests that all language files configured in the main CI4 repository
	 * have a corresponding language file in the current locale.
	 *
	 * @dataProvider localesProvider
	 *
	 * @param string $locale
	 *
	 * @return void
	 */
	final public function testAllConfiguredLanguageFilesAreTranslated(string $locale): void
	{
		$filesNotTranslated = array_diff(
			$this->expectedSets(),
			$this->foundSets($locale)
		);

		sort($filesNotTranslated);
		$count = count($filesNotTranslated);

		self::assertEmpty($filesNotTranslated, sprintf(
			'Failed asserting that language %s "%s" in the main repository %s translated in "%s" locale.',
			$count > 1 ? 'files' : 'file',
			implode('", "', $filesNotTranslated),
			$count > 1 ? 'are' : 'is',
			$locale
		));
	}

	/**
	 * This tests that all translated language files in the current locale have a
	 * corresponding language file in the main CI4 repository.
	 *
	 * @dataProvider localesProvider
	 *
	 * @param string $locale
	 *
	 * @return void
	 */
	final public function testAllTranslatedLanguageFilesAreConfigured(string $locale): void
	{
		$filesNotConfigured = array_diff(
			$this->foundSets($locale),
			$this->expectedSets()
		);

		sort($filesNotConfigured);
		$count = count($filesNotConfigured);

		self::assertEmpty($filesNotConfigured, sprintf(
			'Failed asserting that translated language %s "%s" in "%s" locale %s configured in the main repository.',
			$count > 1 ? 'files' : 'file',
			implode('", "', $filesNotConfigured),
			$locale,
			$count > 1 ? 'are' : 'is'
		));
	}

	/**
	 * This tests that all language keys defined by a language file in the main CI4
	 * repository have corresponding keys in the current locale.
	 *
	 * @dataProvider localesProvider
	 *
	 * @param string $locale
	 *
	 * @return void
	 */
	final public function testAllConfiguredLanguageKeysAreIncluded(string $locale): void
	{
		$keysNotIncluded = [];

		foreach ($this->foundSets($locale) as $file)
		{
			$missing = array_diff_key(
				$this->loadFile($file),
				$this->loadFile($file, $locale)
			);

			foreach (array_keys($missing) as $key)
			{
				$keysNotIncluded[] = substr($file, 0, -4) . '.' . $key;
			}
		}

		sort($keysNotIncluded);
		$count = count($keysNotIncluded);

		self::assertEmpty($keysNotIncluded, sprintf(
			'Failed asserting that the language %s "%s" in the main repository %s included for translation in "%s" locale.',
			$count > 1 ? 'keys' : 'key',
			implode('", "', $keysNotIncluded),
			$count > 1 ? 'are' : 'is',
			$locale
		));
	}

	/**
	 * This tests that all included language keys in a language file for the current
	 * locale have corresponding keys in the main CI4 repository.
	 *
	 * @dataProvider localesProvider
	 *
	 * @param string $locale
	 *
	 * @return void
	 */
	final public function testAllIncludedLanguageKeysAreConfigured(string $locale): void
	{
		$keysNotConfigured = [];

		foreach ($this->foundSets($locale) as $file)
		{
			$extra = array_diff_key(
				$this->loadFile($file, $locale),
				$this->loadFile($file)
			);

			foreach (array_keys($extra) as $key)
			{
				$keysNotConfigured[] = substr($file, 0, -4) . '.' . $key;
			}
		}

		sort($keysNotConfigured);
		$count = count($keysNotConfigured);

		self::assertEmpty($keysNotConfigured, sprintf(
			'Failed asserting that the translated language %s "%s" in "%s" locale %s configured in the main repository.',
			$count > 1 ? 'keys' : 'key',
			implode('", "', $keysNotConfigured),
			$locale,
			$count > 1 ? 'are' : 'is'
		));
	}

	final public function localesProvider(): iterable
	{
		helper('filesystem');
		$locale = self::$locales[static::class] ?? null;

		if (null === $locale)
		{
			$this->fail('The locale code should be defined in the $locales property.');
		}

		return [$locale => [$locale]];
	}

	/**
	 * @dataProvider localesProvider
	 *
	 * @param string $locale
	 *
	 * @return void
	 */
	final public function testLocaleHasCorrespondingTestCaseFile(string $locale): void
	{
		$class = array_flip(self::$locales)[$locale];

		self::assertTrue(class_exists($class, false), sprintf(
			'Failed asserting that test class "%s" is existing.',
			$class
		));
	}

	//-------------------------------------------------------------------------
	// UTILITIES
	//-------------------------------------------------------------------------

	/**
	 * Get all the ISO 639-1 and 639-2 locale codes.
	 *
	 * @return array<string, array<string>>
	 */
	final public function translationKeys(): array
	{
		$sets = [];
		$dirs = directory_map(getcwd() . '/Language', 1);

		foreach ($dirs as $dir)
		{
			$dir = trim($dir, '\\/');
			$sets[$dir] = [$dir];
		}

		return $sets;
	}

	final public function expectedSets(): array
	{
		static $expected;

		if (null === $expected)
		{
			$expected = $this->translationSets();
		}

		return $expected;
	}

	final public function foundSets(string $locale): array
	{
		return $this->translationSets($locale);
	}

	final public function loadFile(string $file, ?string $locale = null): array
	{
		$folder = $locale
			? getcwd() . "/Language/{$locale}/"
			: getcwd() . self::MAIN_LANGUAGE_REPO;

		$file = $folder . $file;

		return require $file;
	}

	/**
	 * Gets the set of language files for each location.
	 *
	 * @param null|string $locale
	 *
	 * @return array
	 */
	private function translationSets(?string $locale = null): array
	{
		$location = $locale
			? getcwd() . "/Language/{$locale}/"
			: getcwd() . self::MAIN_LANGUAGE_REPO;

		$sets = [];
		$files = directory_map($location, 1);

		foreach ($files as $file)
		{
			$sets[$file] = $file;
		}

		return $sets;
	}
}
