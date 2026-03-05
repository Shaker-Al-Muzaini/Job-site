import sys
import re
from deep_translator import GoogleTranslator

input_file = sys.argv[1]
output_file = sys.argv[2]
dialect = sys.argv[3] if len(sys.argv) > 3 else 'standard'

with open(input_file, "r", encoding="utf-8") as f:
    text = f.read()

# Using Google Translate for much higher quality Arabic sentence structure
translator = GoogleTranslator(source='en', target='ar')

# قاموس اللهجات لتحسين واقعية الترجمة
dialect_dict = {
    'eg': {
        r'\bكيف حالك\b': 'إزيك',
        r'\bماذا تفعل\b': 'بتعمل إيه',
        r'\bلماذا\b': 'ليه',
        r'\bالآن\b': 'دلوقتي',
        r'\bجدا\b': 'أوي',
        r'\bرائع\b': 'جامد',
        r'\bسيء\b': 'وحش',
        r'\bجيد\b': 'كويس',
        r'\bأريد\b': 'عايز',
        r'\bنحن\b': 'إحنا',
        r'\bأنتم\b': 'إنتو',
        r'\bهم\b': 'هما',
        r'\bنعم\b': 'أيوة',
        r'\bلا\b': 'لأ',
        r'\bماذا\b': 'إيه',
        r'\bهنا\b': 'هنا',
        r'\bهناك\b': 'هناك',
        r'\bالكثير\b': 'كتير',
        r'\bالقليل\b': 'شوية',
        r'\bأين\b': 'فين',
        r'\bمتى\b': 'إمتى',
        r'\bأرجوك\b': 'لو سمحت',
        r'\bعفوا\b': 'العفو',
        r'\bشكرا\b': 'شكراً',
        r'\bيجب أن\b': 'لازم',
        r'\bهذا\b': 'ده',
        r'\bهذه\b': 'دي',
        r'\bهؤلاء\b': 'دول',
        r'\bهل يمكنك\b': 'ممكن',
        r'\bسوف\b': 'هـ',
        r'\bلن\b': 'مش هـ'
    },
    'gulf': {
        r'\bكيف حالك\b': 'وشلونك',
        r'\bماذا تفعل\b': 'وش تسوي',
        r'\bلماذا\b': 'ليش',
        r'\bالآن\b': 'ألحين',
        r'\bجدا\b': 'وايد',
        r'\bرائع\b': 'كشخة',
        r'\bسيء\b': 'خايس',
        r'\bجيد\b': 'زين',
        r'\bأريد\b': 'أبي',
        r'\bنحن\b': 'حنا',
        r'\bأنتم\b': 'أنتم',
        r'\bهم\b': 'هم',
        r'\bنعم\b': 'إي',
        r'\bلا\b': 'لا',
        r'\bماذا\b': 'وش',
        r'\bهنا\b': 'هني',
        r'\bهناك\b': 'هناك',
        r'\bالكثير\b': 'وايد',
        r'\bالقليل\b': 'شوي',
        r'\bأين\b': 'وين',
        r'\bمتى\b': 'متى',
        r'\bأرجوك\b': 'تكفى',
        r'\bعفوا\b': 'العفو',
        r'\bشكرا\b': 'مشكور',
        r'\bيجب أن\b': 'لازم',
        r'\bهذا\b': 'هذا',
        r'\bهذه\b': 'هذي',
        r'\bهؤلاء\b': 'هذولا',
        r'\bهل يمكنك\b': 'تقدر',
        r'\bسوف\b': 'بـ',
        r'\bلن\b': 'ما راح'
    }
}

def inject_dialect(text, target_dialect):
    if target_dialect not in dialect_dict:
        return text
    
    replacements = dialect_dict[target_dialect]
    for pattern, substitution in replacements.items():
        # استخدام التعبيرات المنتظمة للبحث بذكاء وتجاهل التشكيل أو المسافات
        text = re.sub(pattern, substitution, text)
    return text

def translate_large_text(text, translator, chunk_size=4000):
    paragraphs = text.split("\n")
    translated_paragraphs = []
    current_chunk = ""
    
    for p in paragraphs:
        if len(current_chunk) + len(p) < chunk_size:
            current_chunk += p + "\n"
        else:
            trans = translator.translate(current_chunk)
            trans = inject_dialect(trans, dialect)
            translated_paragraphs.append(trans)
            current_chunk = p + "\n"
            
    if current_chunk.strip():
        trans = translator.translate(current_chunk)
        trans = inject_dialect(trans, dialect)
        translated_paragraphs.append(trans)
        
    return "\n".join(translated_paragraphs)

translated_text = translate_large_text(text, translator)

with open(output_file, "w", encoding="utf-8") as f:
    f.write(translated_text)

