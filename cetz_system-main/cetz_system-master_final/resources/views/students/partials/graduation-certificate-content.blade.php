<style>
    .certificate-page {
        direction: rtl;
        font-family: 'Tajawal', Tahoma, Arial, sans-serif;
    }

    .certificate-sheet {
        position: relative;
        margin: 0 auto;
        width: 210mm;
        min-height: 297mm;
        background: #ffffff;
        color: #111827;
        padding: 172px 56px 58px;
        overflow: hidden;
        box-sizing: border-box;
    }

    .certificate-sheet::before {
        content: '';
        position: absolute;
        inset: 0;
        background: #ffffff;
        pointer-events: none;
    }

    .certificate-body {
        position: relative;
        z-index: 1;
    }

    .certificate-meta {
        display: flex;
        justify-content: space-between;
        gap: 24px;
        min-height: 24px;
        margin-bottom: 26px;
    }

    .certificate-title {
        text-align: center;
        font-size: 31px;
        font-weight: 800;
        margin-bottom: 24px;
        text-shadow: 1px 1px 0 #fff, 2px 2px 0 rgba(0, 0, 0, 0.15);
    }

    .certificate-line {
        line-height: 2;
        font-size: 19px;
        font-weight: 700;
        text-align: center;
        margin-bottom: 11px;
    }

    .certificate-fields {
        margin-bottom: 16px;
    }

    .certificate-field-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        align-items: baseline;
        column-gap: 34px;
        margin-bottom: 4px;
    }

    .certificate-field {
        font-size: 19px;
        font-weight: 800;
        line-height: 1.9;
        white-space: nowrap;
    }

    .certificate-field strong,
    .certificate-line strong {
        font-weight: 900;
    }

    .certificate-note {
        text-align: center;
        font-size: 18px;
        font-weight: 800;
        line-height: 1.95;
        margin-top: 16px;
    }

    .certificate-note .underlined,
    .certificate-footer-warning {
        text-decoration: underline;
        text-decoration-thickness: 2px;
        text-underline-offset: 5px;
    }

    .certificate-signatures {
        display: flex;
        justify-content: space-between;
        gap: 34px;
        margin-top: 64px;
        font-size: 16px;
        font-weight: 700;
    }

    .certificate-signature {
        width: 38%;
        text-align: center;
        line-height: 1.75;
    }

    .certificate-footer-warning {
        margin-top: 82px;
        text-align: center;
        font-size: 20px;
        font-weight: 800;
    }

    .certificate-prepared-by {
        margin-top: 54px;
        text-align: left;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.6;
    }

    @media print {
        html, body {
            width: 210mm;
            height: 297mm;
            margin: 0;
            padding: 0;
            background: #fff;
            overflow: hidden;
        }

        .certificate-page {
            margin: 0;
            padding: 0;
        }

        .certificate-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 0;
            padding: 172px 56px 58px;
            box-shadow: none;
            page-break-after: avoid;
        }

        @page {
            size: A4;
            margin: 0;
        }
    }
</style>

<div class="certificate-sheet">
    <div class="certificate-body">
        <div class="certificate-meta">
            <div>&nbsp;</div>
            <div>&nbsp;</div>
        </div>

        <div class="certificate-title">إفــــــــادة تخرج</div>

        <p class="certificate-line">
            تفيدكم {{ $certificateData['institution_name_ar'] }} بزوارة
        </p>

        <div class="certificate-fields">
            <div class="certificate-field-row">
                <div class="certificate-field">{{ $certificateData['student_label'] }}: <strong>{{ $certificateData['student_name'] }}</strong></div>
                <div class="certificate-field">رقم القيد: <strong>{{ $certificateData['student_number'] }}</strong></div>
            </div>

            <div class="certificate-field-row">
                <div class="certificate-field">الجنسية: <strong>{{ $certificateData['nationality'] }}</strong></div>
                <div class="certificate-field">تاريخ الميلاد: <strong>{{ $certificateData['birth_date'] }} م</strong></div>
            </div>
        </div>

        <p class="certificate-line">
            قد {{ $certificateData['obtained_verb'] }} على شهادة البكالوريوس التقني في
            <strong>{{ $certificateData['department_name'] }}</strong>
            شعبة
            <strong>{{ $certificateData['section_name'] }}</strong>
            بتقدير عام
            <strong>{{ $certificateData['classification'] }}</strong>
            وبنسبة
            <strong>{{ $certificateData['percentage'] }}%</strong>
            للفصل الدراسي
            <strong>{{ $certificateData['graduation_semester'] }}</strong>.
        </p>

        <div class="certificate-note">
            أعدت هذه الإفادة بعد الاطلاع على الملف العلمي {{ $certificateData['student_label_with_lam'] }} وأعطيت ل{{ $certificateData['pronoun_suffix'] }} بناءً على طلب{{ $certificateData['pronoun_suffix'] }}
            <div class="underlined">لاستعمالها في الأغراض المسموح بها قانونًا</div>
        </div>

        <div class="certificate-signatures">
            <div class="certificate-signature">
                <div>يعتمد</div>
                <div>أ. عبد الرؤوف عبد الله غريبة</div>
                <div>المسجل العام</div>
            </div>

            <div class="certificate-signature">
                <div>يعتمد</div>
                <div>أ. وسام يوسف الكعبور</div>
                <div>عميد الكلية</div>
            </div>
        </div>

        <div class="certificate-footer-warning">
            أي كشط أو تعديل يلغيها ولا يعتد إلا بالأصل
        </div>

        <div class="certificate-prepared-by">
            إعداد قسم الخريجين
        </div>
    </div>
</div>
