# Changelog

## Version 0.6.1 `2024-02-16`

* Niirrty.Core was updated to v0.6.1, so also here an update was required.

## Version 0.6.0 `2024-02-15`

* Change Dependencies from `Niirrty.Locale` + `Niirrty.IO.VFS` version `0.5` to `0.6.0`
* Change return type of `Niirrty.Translation.Sources.ISource->reload()` from `Niirrty.Translation.Sources.ISource` to `self`
* Change return type of `Niirrty.Translation.Sources.AbstractSource` methods `->setLocale()`, `->setOption()` and `->setLogger()` from `Niirrty.Translation.Sources.ISource` to `self`
* Change return type of `Niirrty.Translation.ITranslator` methods `->addSource()`, `->removeSource()` and `->cleanSources()` from `Niirrty.Translation.ITranslator` to `self` (also includes all implementing classes)
* Add return type `void` to `Niirrty.Translation.Sources.AbstractSource` methods `logInfo`, `logNotice` and `logWarning`