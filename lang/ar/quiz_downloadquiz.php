<?php
// This file is part of Moodle - https://moodle.org/.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * French language strings for quiz report downloadquiz.
 *
 * @package    quiz_downloadquiz
 * @author     Mario Gharib <mario.gharib@usj.edu.lb | mario.gharib@hotmail.com>
 * @copyright  Mario Gharib 2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


// Plugin identity and privacy.
$string['pluginname']='تنزيل الاختبار مع الإجابات';
$string['downloadquiz']='تنزيل الاختبار مع الإجابات';
$string['downloadquizreport']='تنزيل الاختبار مع الإجابات';
$string['privacy:metadata']='لا يتضمّن تقرير تنزيل الاختبار مرفقًا بالإجابات أيّ تخزينٍ للبيانات الشخصية.';
$string['downloadquiz:view']='تصفح وقم بتنزيل إجابات الاختبار في ملف PDF';


// Report UI.
$string['downloadpdf']='أرسل الاختبار بصيغة PDF عبر البريد الإلكتروني.';
$string['reportintro']='قم بإنشاء ملف PDF مُشفّر ومُؤمَّن يعرض هيكلية الاختبار والإجابات الصحيحة المحفوظة، باستخدام رمز وصول مكوّن من 6 أحرف. يتضمن هذا التصدير:
<ul>
<li>علامات مائية لأغراض التتبّع.</li>
<li>تقييد وظائف النسخ واللصق والطباعة لأسباب أمنية.</li>
<li>استبعاد جميع محاولات الطلاب، والإجابات المُقدَّمة، والدرجات، والتحليلات.</li>
</ul>';

$string['disclaimer']='<b>إخلاء مسؤولية</b>: أنتم على وشك إنشاء تقرير سري بصيغة PDF. يُحظر حظرًا قاطعًا أي استخدام غير مشروع أو توزيع أو إفشاء غير مُصرّح به لمحتواه. وتقع على عاتقكم المسؤولية الكاملة عن الاستخدام السليم لهذا المستند.';
$string['pdfkey']='أدخل مفتاح وصول مكونًا من 6 أحرف على الأقل، ورقم واحد، وحرف كبير واحد.';
$string['pdfkeywarning']='يُرجى حفظ رمز الوصول هذا بشكل آمن. لن يتم تخزينه ولا يمكن استعادته بعد إرسال ملف الـ PDF أو عند تحديث الصفحة';
$string['pdfkeyplaceholder']='رمز وصول مكوّن من ستة محارف أو أحرف';
$string['generatepdfkey']='إنشاء رمز وصول';
$string['showpdfkey']='عرض رمز الوصول';
$string['hidepdfkey']='إخفاء رمز الوصول';
$string['pdfemailsent']='تم إرسال ملف PDF الخاص بهذا الاختبار إلى {$a}.';
$string['usergranttimeleft']='يظلّ وصولك لتنزيل الاختبارات متاحًا لمدة {$a->remaining}. وتنتهي صلاحيته في {$a->expireson}.';


// PDF document labels.
$string['revisioncopy']='نسخة للمراجعة';
$string['generatedon']='صادرة بتاريخ';
$string['course']='الاسم الكامل للمقرر';
$string['question']='السؤال';
$string['correctanswer']='الإجابة الصحيحة';
$string['correctanswers']='الإجابات الصحيحة';
$string['acceptedanswers']='الإجابات المقبولة';
$string['matchingpairs']='أزواج المطابقة الصحيحة';
$string['page']='الصفحة';
$string['maxmark']='الدرجة القصوى';
$string['section']='عنوان';
$string['sectionshuffle']='إخلط الأسئلة';
$string['untitledsection']='عنوان غير محدد';
$string['ofthequiz']='للإختبار';
$string['intentionallyblanknotice']='تم ترك هذه المساحة فارغة عمدًا.';


// PDF metadata and headings.
$string['quiztitle']='عنوان الاختبار:';
$string['quizgeneralinformation']='المعلومات العامة للاختبار:';
$string['quizsecurityinformation']='ضوابط حماية الاختبار:';
$string['assessmentcomposition']='مكوّنات التقييم';
$string['totalquestions']='إجمالي عدد الأسئلة';
$string['quizstartdate']='إفتح الاختبار';
$string['quizenddate']='إغلاق الاختبار';
$string['timelimit']='الوقت المحدد';
$string['attemptsallowed']='المحاولات المسموح بها';
$string['quizmaximumgrade']='الدرجة القصوى للإمتحان';
$string['navigationmethod']='أسلوب التنقل';
$string['gradetopass']='درجة النجاح';
$string['shufflewithinquestions']='إخلط ما ضمن الأسئلة';
$string['quizpassword']='هل تم إدخال كلمة مرور الاختبار؟';
$string['blockconcurrentconnections']='هل تم حظر الاتصالات المتزامنة؟';
$string['networkrestriction']='هل تم تفعيل تقييد الوصول بحسب عنوان IP أو عنوان الشبكة؟';
$string['safeexambrowser']='هل تم تفعيل متصفح الاختبارات الآمن Safe Exam Browser؟';


// Assessment composition labels.
$string['qtypeessay']='الأسئلة مقالية';
$string['qtypemultichoice']='أسئلة متعدد الخيارات';
$string['qtypetruefalse']='أسئلة صح/خطأ';
$string['qtypeshortanswer']='أسئلة الإجابة القصيرة';
$string['qtypematch']='أسئلة المطابقة';
$string['qtypenumerical']='الأسئلة الحسابية';


// Question extraction notes.
$string['unsupportedqtype']='هذا النوع من الأسئلة لا يدعمه نظام تصدير مفتاح الإجابات المنظَّم؛ لذا سيُعرض نصّ السؤال فقط، من دون إدراج أي إجابة في التقرير.';
$string['unsupportedrandom']='تُدرج مواضع الأسئلة العشوائية في التقرير، غير أنه لا يمكن تصدير صيغة ثابتة للأسئلة استنادًا إلى بنية الاختبار وحدها.';
$string['noansweravailable']='لا يُحفظ لهذا النوع من الأسئلة جواب صحيح محدّد. يُعرض نصّ السؤال فقط دون إدراج أي إجابة مُفترَضة.';
$string['fallbackessay']='الأسئلة المقالية لا تتضمّن عادةً إجابة صحيحة نهائية قابلة للتصحيح الآلي؛ لذلك يُصدَّر نصّ السؤال فقط.';
$string['fallbackdescription']='تُعرض عناصر الوصف على شكل نصوص معلوماتية فقط.';
$string['fallbackmultianswer']='قد يحتوي هذا السؤال على أكثر من إجابة صحيحة';
$string['clozefields']='الحقول المُضمَّنة:';
$string['gapselectoptionsheading']='الخيارات المتاحة حسب المجموعة';
$string['gapselectgroupoptions']='المجموعة {$a->group}: {$a->options}';
$string['group']='المجموعة';
$string['shufflechoicesstatus']='هل تمّ خلط الخيارات؟ {$a}';
$string['mcqanswertype']='نوع الإجابة: {$a}';
$string['mcqsingle']='إجابة واحدة فقط';
$string['mcqmultiple']='يُسمح باختيار عدة إجابات';


// Admin settings and grant management.
$string['settingsintro']='يُرجى استخدام صفحة الإدارة أدناه لمنح أو سحب حق وصول المستخدمين—لفترة زمنية محدّدة—إلى ميزة تنزيل ملفات الاختبارات بصيغة PDF. تسري هذه الصلاحيات على جميع الاختبارات، وتُمنح حصراً للمستخدمين الذين يتمتّعون أصلًا بصلاحيات الوصول المعتادة إلى تلك الاختبارات ضمن نظام مودل';
$string['managegrants']='إدارة صلاحيات وصول المستخدمين المحدَّدة زمنيًا';
$string['managegrantslink']='فتح إدارة صلاحيات الوصول المحدَّدة زمنيًا';
$string['managegrantsdesc']='يُمنح حق الوصول عبر إدخال عنوان البريد الإلكتروني للمستخدم وتحديد تاريخ انتهاء الصلاحية. ويؤدي إعادة إدخال العنوان نفسه إلى تحديث الصلاحية القائمة وإعادة تعيين تاريخ انتهائها';
$string['currentgrants']='الصلاحيات النشطة حاليًا';
$string['nograntsconfigured']='لا توجد حاليًا أي صلاحيات وصول محدَّدة زمنيًا مُفعّلة.';
$string['downloadcsv']='تنزيل ملف CSV (الصلاحيات النشطة)';
$string['grantroleid']='دور الوصول المحدَّد زمنيًا';
$string['requiredrolemissing']='الدور المطلوب "Download Quiz PDF Access" ذو الاسم المختصر "downloadquizaccess" غير موجود حاليًا.';
$string['createrequiredrole']='إنشاء الدور المطلوب';
$string['requiredroleexists']='الدور المطلوب موجود مسبقًا، وقد تم ربط الإضافة به تلقائيًا.';
$string['requiredrolecreated']='تم إنشاء الدور المطلوب بنجاح، وتم ربطه بالمكوّن الإضافي plugin تلقائيًا.';


// Grant form and grant table.
$string['useremail']='البريد الإلكتروني للمستخدم';
$string['userfullname']='المستخدم';
$string['grantedby']='مُنِح بواسطة';
$string['timegranted']='مُنِح بتاريخ';
$string['expirestime']='تاريخ انتهاء الصلاحية';
$string['timeleft']='الوقت المتبقي';
$string['savegrant']='حفظ الصلاحية';
$string['grantsaved']='تم حفظ صلاحية الوصول المحدَّدة زمنيًا للمستخدم {$a}.';
$string['grantrevoked']='تم سحب صلاحية الوصول المحدَّدة زمنيًا.';
$string['revokegrant']='سحب الصلاحية';


// Email delivery.
$string['emailsubject']='CONFIDENTIAL PDF of the Quiz - {$a->filename}';
$string['emailbodytext'] = 'إلى {$a->fullname},

تم إنشاء نسخة بصيغة PDF من اختبار "{$a->quizname}" ضمن المقرر "{$a->coursename}" وذلك يوم "{$a->generateddate}" وقد أُرفقت بهذه الرسالة

هذا الملف بصيغة PDF مُشفّر ومُحمي بمفتاح وصول مكوّن من 6 محارف أو أحرف تم إنشاؤه عند تقديم الطلب. يتضمّن علامة مائية لأغراض التتبّع، كما أن وظائف النسخ واللصق والطباعة مقيّدة لأسباب أمنية.

يرجى استخدام هذا المفتاح المكوّن من 6 محارف أو أحرف لفتح ملف الـ PDF المحمي المرفق.

<p style="color:#d32f2f; font-weight:bold;">هذا المستند سري. يُحظر تداوله أو مشاركته ما لم يكن ذلك مصرحًا به. وتُعدّ أي إساءة استخدام أو توزيع أو إفشاء غير مصرح به لمحتواه مسؤولية {$a->fullname} وحده</p>';
$string['errorpdfemailsend']='تم إنشاء ملف PDF، لكن تعذّر إرسال البريد الإلكتروني.';


// Events and confidentiality.
$string['eventquizdownloaded']='تم تنزيل الاختبار';
$string['confidentialnoticeprefix']='إخلاء المسؤولية';
$string['confidentialnoticefull']='تم إنشاء هذا التقرير بصيغة PDF بناءً على طلب المستخدم: {$a->user}. تقع على عاتق المستخدم الطالب وحده المسؤولية الكاملة عن أي إساءة استخدام أو توزيع أو إفشاء غير مُصرّح به لمحتوى هذا التقرير.';


// Status values.
$string['yes']='نعم';
$string['no']='كلا';
$string['true'] = 'صح';
$string['false'] = 'خطأ';
$string['notset']='غير محدد';
$string['notdefined']='غير معرّف';
$string['unlimitedattempts']='بلا حدود';
$string['expired']='منتهي الصلاحية';


// Errors and validation.
$string['errorusernotfound']='لم يتم العثور على أي مستخدم نشط في نظام مودل مرتبط بعنوان البريد الإلكتروني هذا.';
$string['errorexpiryinvalid']='يجب أن يكون تاريخ انتهاء الصلاحية محدد في المستقبل.';
$string['errorpdfkeyrequiredclient']='يجب إدخال رمز وصول هنا.';
$string['errorpdfkeylength']='يجب أن يتكوّن رمز الوصول من ستة محارف أو أحرف بالضبط.';
$string['errorpdfkeyrequired']='يجب أن يحتوي مفتاح الوصول على 6 أحرف على الأقل، ورقم واحد، وحرف كبير واحد.';
$string['errorpdfgeneration']='تعذّر إنشاء ملف PDF.';
$string['errornoquestions']='لم يتم العثور على أي أسئلة قابلة للتصدير في هذا الاختبار.';
$string['errornoroleselected']='لم يتم إعداد دور الوصول المحدَّد زمنيًا في إعدادات المكوّن الإضافي plugin.';