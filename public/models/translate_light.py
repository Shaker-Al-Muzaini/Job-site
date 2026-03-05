from argostranslate import package, translate

# تثبيت النموذج (مرة واحدة)
package.install_from_path("models/translate-en_ar-1_0.argosmodel")

installed_languages = translate.get_installed_languages()
from_lang = next(filter(lambda x: x.code == "en", installed_languages))
to_lang = next(filter(lambda x: x.code == "ar", installed_languages))
translation = from_lang.get_translation(to_lang)

with open("audio/audio.txt", "r", encoding="utf-8") as f:
    text = f.read()

translated_text = translation.translate(text)

with open("audio/arabic.txt", "w", encoding="utf-8") as f:
    f.write(translated_text)
