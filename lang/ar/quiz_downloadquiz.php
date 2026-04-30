<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Arabic language strings for quiz report downloadquiz.
 *
 * @package   quiz_downloadquiz
 * @copyright 2026 Center for Digital Innovation and Artificial Intelligence <moodle.cinia@usj.edu.lb>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['acceptedanswers'] = 'الإجابات المقبولة';
$string['assessmentcomposition'] = 'مكوّنات التقييم';
$string['attemptsallowed'] = 'المحاولات المسموح بها';
$string['blockconcurrentconnections'] = 'هل تم حظر الاتصالات المتزامنة؟';
$string['clozefields'] = 'الحقول المُضمَّنة:';
$string['confidentialnoticefull'] = 'تم إنشاء هذا التقرير بصيغة PDF بناءً على طلب المستخدم: {$a->user}. تقع على عاتق المستخدم الطالب وحده المسؤولية الكاملة عن أي إساءة استخدام أو توزيع أو إفشاء غير مُصرّح به لمحتوى هذا التقرير.';
$string['confidentialnoticeprefix'] = 'إخلاء المسؤولية';
$string['correctanswer'] = 'الإجابة الصحيحة';
$string['correctanswers'] = 'الإجابات الصحيحة';
$string['course'] = 'الاسم الكامل للمقرر';
$string['createrequiredrole'] = 'إنشاء الدور المطلوب';
$string['currentgrants'] = 'الصلاحيات النشطة حاليًا';
$string['disclaimer'] = '<b>إخلاء مسؤولية</b>: أنتم على وشك إنشاء تقرير سري بصيغة PDF. يُحظر حظرًا قاطعًا أي استخدام غير مشروع أو توزيع أو إفشاء غير مُصرّح به لمحتواه. وتقع على عاتقكم المسؤولية الكاملة عن الاستخدام السليم لهذا المستند.';
$string['downloadcsv'] = 'تنزيل ملف CSV (الصلاحيات النشطة)';
$string['downloadpdf'] = 'أرسل الاختبار بصيغة PDF عبر البريد الإلكتروني.';
$string['downloadquiz'] = 'تنزيل الاختبار مع الإجابات';
$string['downloadquiz:view'] = 'تصفح وقم بتنزيل إجابات الاختبار في ملف PDF';
$string['downloadquizreport'] = 'تنزيل الاختبار مع الإجابات';
$string['emailbodytext'] = 'إلى {$a->fullname},

تم إنشاء نسخة بصيغة PDF من اختبار "{$a->quizname}" ضمن المقرر "{$a->coursename}" وذلك يوم "{$a->generateddate}" وقد أُرفقت بهذه الرسالة

هذا الملف بصيغة PDF مُشفّر ومُحمي بمفتاح وصول مكوّن من 6 محارف أو أحرف تم إنشاؤه عند تقديم الطلب. يتضمّن علامة مائية لأغراض التتبّع، كما أن وظائف النسخ واللصق والطباعة مقيّدة لأسباب أمنية.

يرجى استخدام هذا المفتاح المكوّن من 6 محارف أو أحرف لفتح ملف الـ PDF المحمي المرفق.

<p style="color:#d32f2f; font-weight:bold;">هذا المستند سري. يُحظر تداوله أو مشاركته ما لم يكن ذلك مصرحًا به. وتُعدّ أي إساءة استخدام أو توزيع أو إفشاء غير مصرح به لمحتواه مسؤولية {$a->fullname} وحده</p>';
$string['emailsubject'] = 'CONFIDENTIAL PDF of the Quiz - {$a->filename}';
$string['errorexpiryinvalid'] = 'يجب أن يكون تاريخ انتهاء الصلاحية محدد في المستقبل.';
$string['errornoquestions'] = 'لم يتم العثور على أي أسئلة قابلة للتصدير في هذا الاختبار.';
$string['errornoroleselected'] = 'لم يتم إعداد دور الوصول المحدَّد زمنيًا في إعدادات المكوّن الإضافي plugin.';
$string['errorpdfemailsend'] = 'تم إنشاء ملف PDF، لكن تعذّر إرسال البريد الإلكتروني.';
$string['errorpdfgeneration'] = 'تعذّر إنشاء ملف PDF.';
$string['errorpdfkeylength'] = 'يجب أن يتكوّن رمز الوصول من ستة محارف أو أحرف بالضبط.';
$string['errorpdfkeyrequired'] = 'يجب أن يحتوي مفتاح الوصول على 6 أحرف على الأقل، ورقم واحد، وحرف كبير واحد.';
$string['errorpdfkeyrequiredclient'] = 'يجب إدخال رمز وصول هنا.';
$string['errorusernotfound'] = 'لم يتم العثور على أي مستخدم نشط في نظام مودل مرتبط بعنوان البريد الإلكتروني هذا.';
$string['eventquizdownloaded'] = 'تم تنزيل الاختبار';
$string['expired'] = 'منتهي الصلاحية';
$string['expirestime'] = 'تاريخ انتهاء الصلاحية';
$string['fallbackdescription'] = 'تُعرض عناصر الوصف على شكل نصوص معلوماتية فقط.';
$string['fallbackessay'] = 'الأسئلة المقالية لا تتضمّن عادةً إجابة صحيحة نهائية قابلة للتصحيح الآلي؛ لذلك يُصدَّر نصّ السؤال فقط.';
$string['fallbackmultianswer'] = 'قد يحتوي هذا السؤال على أكثر من إجابة صحيحة';
$string['false'] = 'خطأ';
$string['gapselectgroupoptions'] = 'المجموعة {$a->group}: {$a->options}';
$string['gapselectoptionsheading'] = 'الخيارات المتاحة حسب المجموعة';
$string['generatedon'] = 'صادرة بتاريخ';
$string['generatepdfkey'] = 'إنشاء رمز وصول';
$string['gradetopass'] = 'درجة النجاح';
$string['grantedby'] = 'مُنِح بواسطة';
$string['grantrevoked'] = 'تم سحب صلاحية الوصول المحدَّدة زمنيًا.';
$string['grantroleid'] = 'دور الوصول المحدَّد زمنيًا';
$string['grantsaved'] = 'تم حفظ صلاحية الوصول المحدَّدة زمنيًا للمستخدم {$a}.';
$string['group'] = 'المجموعة';
$string['hidepdfkey'] = 'إخفاء رمز الوصول';
$string['intentionallyblanknotice'] = 'تم ترك هذه المساحة فارغة عمدًا.';
$string['managegrants'] = 'إدارة صلاحيات وصول المستخدمين المحدَّدة زمنيًا';
$string['managegrantsdesc'] = 'يُمنح حق الوصول عبر إدخال عنوان البريد الإلكتروني للمستخدم وتحديد تاريخ انتهاء الصلاحية. ويؤدي إعادة إدخال العنوان نفسه إلى تحديث الصلاحية القائمة وإعادة تعيين تاريخ انتهائها';
$string['managegrantslink'] = 'فتح إدارة صلاحيات الوصول المحدَّدة زمنيًا';
$string['matchingpairs'] = 'أزواج المطابقة الصحيحة';
$string['maxmark'] = 'الدرجة القصوى';
$string['mcqanswertype'] = 'نوع الإجابة: {$a}';
$string['mcqmultiple'] = 'يُسمح باختيار عدة إجابات';
$string['mcqsingle'] = 'إجابة واحدة فقط';
$string['navigationmethod'] = 'أسلوب التنقل';
$string['networkrestriction'] = 'هل تم تفعيل تقييد الوصول بحسب عنوان IP أو عنوان الشبكة؟';
$string['no'] = 'كلا';
$string['noansweravailable'] = 'لا يُحفظ لهذا النوع من الأسئلة جواب صحيح محدّد. يُعرض نصّ السؤال فقط دون إدراج أي إجابة مُفترَضة.';
$string['nograntsconfigured'] = 'لا توجد حاليًا أي صلاحيات وصول محدَّدة زمنيًا مُفعّلة.';
$string['notdefined'] = 'غير معرّف';
$string['notset'] = 'غير محدد';
$string['ofthequiz'] = 'للإختبار';
$string['page'] = 'الصفحة';
$string['pdfemailsent'] = 'تم إرسال ملف PDF الخاص بهذا الاختبار إلى {$a}.';
$string['pdfkey'] = 'أدخل مفتاح وصول مكونًا من 6 أحرف على الأقل، ورقم واحد، وحرف كبير واحد.';
$string['pdfkeyplaceholder'] = 'رمز وصول مكوّن من ستة محارف أو أحرف';
$string['pdfkeywarning'] = 'يُرجى حفظ رمز الوصول هذا بشكل آمن. لن يتم تخزينه ولا يمكن استعادته بعد إرسال ملف الـ PDF أو عند تحديث الصفحة';
$string['pluginname'] = 'تنزيل الاختبار مع الإجابات';
$string['privacy:metadata'] = 'يقوم تقرير "تنزيل الاختبار مرفقًا الإجابات" بتخزين صلاحيات وصول مؤقتة للمستخدمين.';
$string['privacy:metadata:quiz_downloadquiz_grants'] = 'يخزن صلاحيات الوصول المؤقتة لتنزيل ملفات PDF الخاصة بالاختبارات.';
$string['privacy:metadata:quiz_downloadquiz_grants:enabled'] = 'يشير إلى ما إذا كان الوصول مفعّلاً حالياً.';
$string['privacy:metadata:quiz_downloadquiz_grants:grantedby'] = 'المستخدم الذي منح الوصول.';
$string['privacy:metadata:quiz_downloadquiz_grants:timecreated'] = 'الطابع الزمني لإنشاء السجل.';
$string['privacy:metadata:quiz_downloadquiz_grants:timeexpires'] = 'تاريخ ووقت انتهاء صلاحية الوصول.';
$string['privacy:metadata:quiz_downloadquiz_grants:timegranted'] = 'تاريخ ووقت منح الوصول.';
$string['privacy:metadata:quiz_downloadquiz_grants:timemodified'] = 'الطابع الزمني لآخر تعديل على السجل.';
$string['privacy:metadata:quiz_downloadquiz_grants:userid'] = 'المستخدم الذي مُنح له الوصول.';
$string['qtypeessay'] = 'الأسئلة مقالية';
$string['qtypematch'] = 'أسئلة المطابقة';
$string['qtypemultichoice'] = 'أسئلة متعدد الخيارات';
$string['qtypenumerical'] = 'الأسئلة الحسابية';
$string['qtypeshortanswer'] = 'أسئلة الإجابة القصيرة';
$string['qtypetruefalse'] = 'أسئلة صح/خطأ';
$string['question'] = 'السؤال';
$string['quizenddate'] = 'إغلاق الاختبار';
$string['quizgeneralinformation'] = 'المعلومات العامة للاختبار:';
$string['quizmaximumgrade'] = 'الدرجة القصوى للإمتحان';
$string['quizpassword'] = 'هل تم إدخال كلمة مرور الاختبار؟';
$string['quizsecurityinformation'] = 'ضوابط حماية الاختبار:';
$string['quizstartdate'] = 'إفتح الاختبار';
$string['quiztitle'] = 'عنوان الاختبار:';
$string['reportintro'] = 'قم بإنشاء ملف PDF مُشفّر ومُؤمَّن يعرض هيكلية الاختبار والإجابات الصحيحة المحفوظة، باستخدام رمز وصول مكوّن من 6 أحرف. يتضمن هذا التصدير:
<ul>
<li>علامات مائية لأغراض التتبّع.</li>
<li>تقييد وظائف النسخ واللصق والطباعة لأسباب أمنية.</li>
<li>استبعاد جميع محاولات الطلاب، والإجابات المُقدَّمة، والدرجات، والتحليلات.</li>
</ul>';
$string['requiredrolecreated'] = 'تم إنشاء الدور المطلوب بنجاح، وتم ربطه بالمكوّن الإضافي plugin تلقائيًا.';
$string['requiredroleexists'] = 'الدور المطلوب موجود مسبقًا، وقد تم ربط الإضافة به تلقائيًا.';
$string['requiredrolemissing'] = 'الدور المطلوب "Download Quiz PDF Access" ذو الاسم المختصر "downloadquizaccess" غير موجود حاليًا.';
$string['revisioncopy'] = 'نسخة للمراجعة';
$string['revokegrant'] = 'سحب الصلاحية';
$string['safeexambrowser'] = 'هل تم تفعيل متصفح الاختبارات الآمن Safe Exam Browser؟';
$string['savegrant'] = 'حفظ الصلاحية';
$string['section'] = 'عنوان';
$string['sectionshuffle'] = 'إخلط الأسئلة';
$string['settingsintro'] = 'يُرجى استخدام صفحة الإدارة أدناه لمنح أو سحب حق وصول المستخدمين—لفترة زمنية محدّدة—إلى ميزة تنزيل ملفات الاختبارات بصيغة PDF. تسري هذه الصلاحيات على جميع الاختبارات، وتُمنح حصراً للمستخدمين الذين يتمتّعون أصلًا بصلاحيات الوصول المعتادة إلى تلك الاختبارات ضمن نظام مودل';
$string['showpdfkey'] = 'عرض رمز الوصول';
$string['shufflechoicesstatus'] = 'هل تمّ خلط الخيارات؟ {$a}';
$string['shufflewithinquestions'] = 'إخلط ما ضمن الأسئلة';
$string['timegranted'] = 'مُنِح بتاريخ';
$string['timeleft'] = 'الوقت المتبقي';
$string['timelimit'] = 'الوقت المحدد';
$string['totalquestions'] = 'إجمالي عدد الأسئلة';
$string['true'] = 'صح';
$string['unlimitedattempts'] = 'بلا حدود';
$string['unsupportedqtype'] = 'هذا النوع من الأسئلة لا يدعمه نظام تصدير مفتاح الإجابات المنظَّم؛ لذا سيُعرض نصّ السؤال فقط، من دون إدراج أي إجابة في التقرير.';
$string['unsupportedrandom'] = 'تُدرج مواضع الأسئلة العشوائية في التقرير، غير أنه لا يمكن تصدير صيغة ثابتة للأسئلة استنادًا إلى بنية الاختبار وحدها.';
$string['untitledsection'] = 'عنوان غير محدد';
$string['useremail'] = 'البريد الإلكتروني للمستخدم';
$string['userfullname'] = 'المستخدم';
$string['usergranttimeleft'] = 'يظلّ وصولك لتنزيل الاختبارات متاحًا لمدة {$a->remaining}. وتنتهي صلاحيته في {$a->expireson}.';
$string['yes'] = 'نعم';
