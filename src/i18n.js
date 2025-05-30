import Vue from 'vue';
import VueI18n from 'vue-i18n';

Vue.use(VueI18n);

const messages = {
  en: {
    
  popup: {
    "message": "Do you accept the terms and conditions?",
    "accept": "Accept",
    "reject": "Reject"
  },


    login: {
      title: 'Log In',
      noAccount: "Don't Have An Account?",
      signUp: 'Sign Up',
      email: 'Email Address',
      password: 'Password',
      loginButton: 'Log In',
      forgotPassword: 'Forgot Password?',
      googleLogin: 'Log In With Google',
      backHome: 'Back To Home Page',
      language:'English',
    },
    
    signup: {
      title: 'Sign Up',
      haveAccount: 'Already have an account?',
      login: 'Log In',
      username: 'Username',
      email: 'Email Address',
      password: 'Password',
      confirmPassword: 'Confirm Password',
      signUpButton: 'Sign Up',
      backHome: 'Back To Home Page'
    },
    
    notificationSettings: {
      home: '  Home',
      invoicesReports: '  Invoices And Reports',
      servicesLink: '  Services Link',
      videos: '  Videos',
      affiliate: '  Affiliate',
      contactUs: '  Contact Us',
      permissions: '  Permissions',
      language: 'Language : ',
      nightMode: 'Night mode',
      downloadApps: 'DOWNLOAD APPS',
      notificationSetting: 'Notification Setting',
      addTestimonial: 'Add New Testimonial',
      invoicesAndReports:'invoices And Reports',
      systemNotification: 'System Notification',
      systemNotificationDesc: 'All Notifications Sending By GitTax Team.',
      billingCreated: 'Billing Created',
      billingCreatedDesc: 'All Bills Created By Source Or You Will Make A Successful Notification.',
      backupMaker: 'Backup Maker',
      backupMakerDesc: 'When Your Backup Sent to Your Mail Will Get A Successful Notification.',
      gotFreeMonth: 'Got Free Month',
      gotFreeMonthDesc: 'If Someone Uses Your Affiliate Code You Will Get Free 1 Month Just One Time.',
      testimonials: 'Testimonials',
      merchant: 'Merchant',
      categories: 'Categories',
      booking: 'Booking',
      pricing: 'Pricing',
      invoices:'invoices',
      ads: 'Ads',
      content: 'Content',
      settlement: 'Settlement',
      setting: 'Setting',
      reports: 'Reports',
      reviews: 'Reviews',
      logout:'logout',
      profile:'profile'

    },
    addTestimonial: {
        title: "Add New Testimonial",
        userPhoto: "User Photo",
        dropImage: "Drop Your Image Here Or Browse",
        maxSize: "Max Size 5 MB , 35*35 Pixel Supported Format .Jpg, .Png",
        username: "User Name",
        companyName: "Company Name",
        content: "Content",
        save: "Save"
      },
    reports: {
      title: 'Reports',
      selectTime: 'Select Time:',
      exportPdf: 'Export To PDF File',
      exportExcel: 'Export To Excel File',
      totalSales: 'Total Sales',
      totalSalesDesc: 'All Invoice With Many Details You Can See As Clients And Amounts And Every Things',
      totalFees: 'Total Administrative Fees',
      totalFeesDesc: 'All Invoice With Many Details You Can See As Clients And Amounts And Every Things',
      playgrounds: 'Playgrounds',
      playgroundsDesc: 'All Invoice With Many Details You Can See As Clients And Amounts And Every Things',
      totalSubs: 'Total Subscription Sales',
      totalSubsDesc: 'All Invoice With Many Details You Can See As Clients And Amounts And Every Things',
      clients: 'Clients',
      clientsDesc: 'All Invoice With Many Details You Can See As Clients And Amounts And Every Things',
      bankFee: 'Bank Fee',
      bankFeeDesc: 'All Invoice With Many Details You Can See As Clients And Amounts And Every Things'
      ,from:'Select Time '
      ,to:''

    },
    dashboard: {
        title: 'Dashboard',
        welcome: 'Welcome',
        notificationSettings: 'Notification Settings',
        reports: 'Reports',
        logout:'logout',
      },
  },
  ar: {
    
  popup: {
    "message": "هل تقبل الشروط والأحكام؟",
    "accept": "موافق",
    "reject": "رفض"
  },

    dashboard: {
        title: 'لوحة القيادة',
        welcome: 'أهلا بك',
        notificationSettings: 'إعدادات الإشعارات',
        reports: 'التقارير',
        logout:'تسجيل الخروج',
      },
    login: {
      title: 'تسجيل الدخول',
      noAccount: 'لا تملك حساب؟',
      signUp: 'اشتراك',
      email: 'عنوان البريد الإلكتروني',
      password: 'كلمة المرور',
      loginButton: 'تسجيل الدخول',
      forgotPassword: 'هل نسيت كلمة السر؟',
      googleLogin: 'تسجيل الدخول باستخدام جوجل',
      backHome: 'العودة إلى الصفحة الرئيسية',
      language:'اللغة العربية',
    },
    addTestimonial: {
        title: "إضافة شهادة جديدة",
        userPhoto: "صورة المستخدم",
        dropImage: "قم بإسقاط صورتك هنا أو تصفح",
        maxSize: "الحجم الأقصى 5 ميجا بايت ، 35*35 بكسل ، التنسيق المدعوم .Jpg, .Png",
        username: "اسم المستخدم",
        companyName: "اسم الشركة",
        content: "المحتوى",
        save: "حفظ"
      },
    signup: {
      title: 'اشتراك',
      haveAccount: 'هل لديك حساب؟',
      login: 'تسجيل الدخول',
      username: 'اسم المستخدم',
      email: 'عنوان البريد الإلكتروني',
      password: 'كلمة المرور',
      confirmPassword: 'تأكيد كلمة المرور',
      signUpButton: 'اشتراك',
      backHome: 'العودة إلى الصفحة الرئيسية'
    },
    notificationSettings: {
      home: 'الصفحة الرئيسية',
      invoicesAndReports:'الفواتير والتقارير',
      invoices:'الفواتير',
      invoicesReports: 'الفواتير والتقارير',
      servicesLink: 'رابط الخدمات',
      videos: 'مقاطع فيديو',
      affiliate: 'التابعة',
      contactUs: 'اتصل بنا',
      permissions: 'الأذونات',
      language: ' : اللغة',
      nightMode: 'الوضع الليلي',
      downloadApps: 'تنزيل التطبيقات',
      notificationSetting: 'إعدادات الإشعارات',
      addTestimonial: 'إضافة شهادة جديدة',
      systemNotification: 'إشعار النظام',
      systemNotificationDesc: 'جميع الإشعارات مرسلة من قبل فريق GitTax.',
      billingCreated: 'تم إنشاء الفاتورة',
      billingCreatedDesc: 'جميع الفواتير التي تم إنشاؤها بواسطة المصدر أو ستحصل على إشعار ناجح.',
      backupMaker: 'صانع النسخ الاحتياطي',
      backupMakerDesc: 'عند إرسال النسخة الاحتياطية إلى بريدك الإلكتروني ، ستحصل على إشعار ناجح.',
      gotFreeMonth: 'حصلت على شهر مجاني',
      gotFreeMonthDesc: 'إذا استخدم شخص ما رمز الإحالة الخاص بك ، ستحصل على شهر مجاني مرة واحدة فقط.',
      testimonials: 'الشهادات',
      merchant: 'التاجر',
      categories: 'الفئات',
      booking: 'الحجز',
      pricing: 'التسعير',
      ads: 'الإعلانات',
      content: 'المحتوى',
      settlement: 'التسوية',
      setting: 'الإعدادات',
      reports: 'التقارير',
      reviews: 'المراجعات',
      logout:'تسجيل الخروج',
      profile:'حساب تعريفي'
    },
    reports: {
      title: 'التقارير',
      selectTime: 'اختر الوقت:',
      exportPdf: 'تصدير إلى ملف PDF',
      exportExcel: 'تصدير إلى ملف Excel',
      totalSales: 'إجمالي المبيعات',
      totalSalesDesc: 'جميع الفواتير مع الكثير من التفاصيل التي يمكنك رؤيتها كعملاء ومبالغ وكل شيء',
      totalFees: 'إجمالي الرسوم الإدارية',
      totalFeesDesc: 'جميع الفواتير مع الكثير من التفاصيل التي يمكنك رؤيتها كعملاء ومبالغ وكل شيء',
      playgrounds: 'الملاعب',
      playgroundsDesc: 'جميع الفواتير مع الكثير من التفاصيل التي يمكنك رؤيتها كعملاء ومبالغ وكل شيء',
      totalSubs: 'إجمالي مبيعات الاشتراكات',
      totalSubsDesc: 'جميع الفواتير مع الكثير من التفاصيل التي يمكنك رؤيتها كعملاء ومبالغ وكل شيء',
      clients: 'العملاء',
      clientsDesc: 'جميع الفواتير مع الكثير من التفاصيل التي يمكنك رؤيتها كعملاء ومبالغ وكل شيء',
      bankFee: 'رسوم البنك',
      bankFeeDesc: 'جميع الفواتير مع الكثير من التفاصيل التي يمكنك رؤيتها كعملاء ومبالغ وكل شيء'
      ,from:'حدد التاريخ  '
      ,to:''

    }
  }
};

const i18n = new VueI18n({
  locale: 'en', // Set locale
  fallbackLocale: 'en', // Set fallback locale
  messages // Set locale messages
});

export default i18n;
