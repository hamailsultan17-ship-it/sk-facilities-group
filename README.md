# SK Facilities Group – Landing Page (HTML / CSS / JS / PHP / MySQL, XAMPP)

## 1. XAMPP par chalane ka tareeqa (5 minute)
1. XAMPP Control Panel mein **Apache** aur **MySQL** dono **Start** karo.
2. Ye poora folder `sk-facilities-xampp` copy karke `C:\xampp\htdocs\` mein rakh do.
3. Browser mein kholo: **http://localhost/sk-facilities-xampp/**
4. Database `sk_facilities` aur tables **pehli dafa khud ban jate hain** (kuch import nahi karna).
   Chahein to `sql/schema.sql` phpMyAdmin se import kar sakte ho.

> MySQL settings `includes/config.php` mein set hain: user `root`, password khali, port **3307** (agar aapka MySQL 3306 par ho to `DB_PORT` badal do; code dono ports try bhi karta hai).

## 2. Form submit par kya hota hai
- User ka data (naam, email, phone, business, service, message, IP, time) MySQL ke `enquiries` table mein save hota hai.
- Submit button ke **neeche** "Your message has been sent successfully!" + reference number (jaise `SK-20261017-A13D`) aata hai, aur **form khud blank ho jata hai** taake usi page par naya message bhej sako (page refresh ki zaroorat nahi).
- User ko **confirmation email** milti hai, aur company ko **new enquiry alert** (Reply-To customer ki email hoti hai).
- Email fail ho jaye to bhi enquiry save rehti hai.

### Email chalane ke liye (zaroori)
XAMPP ka `mail()` aam tor par kaam nahi karta, isliye SMTP use hota hai. `includes/config.php` mein bharo:
```php
const SMTP_HOST = 'smtp.gmail.com';
const SMTP_PORT = 587;
const SMTP_SECURE = 'tls';
const SMTP_USER = 'apka@gmail.com';
const SMTP_PASS = 'gmail-app-password';   // Google Account > Security > App passwords
const MAIL_NOTIFY_TO = 'skfacilities@outlook.com';  // company ko alert yahan aayega
```
Outlook ke liye: host `smtp-mail.outlook.com`, port 587, secure `tls`, user `skfacilities@outlook.com` (2-step verification on ho to App password banao).
Email bhejna visitor ke confirmation ke **baad** hota hai, is liye SMTP slow ya galat ho to bhi screen par confirmation foran aati hai. Error ki wajah `xampp/apache/logs/error.log` mein milti hai.
Jab tak SMTP khali hai, form save hota hai lekin email nahi jati.


### Kaise pata chale ke form ka data database mein save hua? (3 tareeqe)
1. **Admin panel:** http://localhost/sk-facilities-xampp/admin/ (user `admin`). Har enquiry ki row yahan aati hai, saath mein **Mail** column batata hai ke customer/company ko email gayi ya nahi (na jaye to wajah bhi likhi hoti hai).
2. **check.php:** http://localhost/sk-facilities-xampp/check.php. Database connection, tables, aakhri 10 enquiries aur **"Send a test email"** ka button yahan hai.
3. **phpMyAdmin:** http://localhost/phpmyadmin, database `sk_facilities`, table `enquiries`.

Submit ke baad user ko screen par **"Message sent successfully!"** aur reference number dikhta hai, aur uski email par **"We received your message"** wali email jati hai (SMTP set hone par).

## 2b. Chat (auto-reply bot + live staff)
- Neeche-right ke chat button par click karo: bot **har message ka foran jawab** deta hai ("typing..." ke baad) aur saath mein tap karne wale option buttons (Our services, Get a quote, Contact details, Talk to a person) dikhata hai. Chat ka rang/design website ki theme (teal + orange) ke mutabiq hai.
- Bot ke jawab `includes/bot.php` mein hain. Phone, email, address aur hours `includes/config.php` (`$SITE`) se khud uthte hain. Naye sawal/jawab ya keywords wahin add karo.
- Bot rule-based hai (AI nahi). Jo sawal samajh na aaye, wahan bot kehta hai ke team ko pass kar diya aur contact details maangta hai.
- Visitor chat mein email likhe to wo chat ke saath save ho jati hai (admin mein dikhti hai).
- Sab messages database (`chat_sessions`, `chat_messages`) mein save hote hain. **Staff jawab:** http://localhost/sk-facilities-xampp/admin/chat.php (admin login ke baad). Jab tak ye page khula hai visitor ko "Team online" dikhta hai. Staff reply kare to agle 10 minute bot chup rehta hai taake dono ek saath jawab na dein.
- Agar visitor ne email di thi aur wo page chhor chuka ho, to staff ka reply uski email par bhi chala jata hai (SMTP set ho to). Nayi chat par company email par alert aata hai.
- Spam se bachao: ek IP se 1 ghante mein 6 nayi chats aur 1 minute mein 15 messages tak.

## 3. Admin panel
- Link: **http://localhost/sk-facilities-xampp/admin/**
- Username: `admin`  |  Password: `SK@Admin2026`  (**zaroor badlo**)
- Password badalne ke liye: `admin/make-hash.php?p=NayaPassword` kholo, jo hash aaye wo `includes/config.php` ke `ADMIN_PASS_HASH` mein paste karo, phir `make-hash.php` delete kar do.
- Is mein sab enquiries, search, pagination aur **Download CSV** (Excel mein khulta hai) hai.

### Form mein error aaye to
`includes/config.php` mein `DEBUG = true` hai: form ke upar ke lal box mein asal wajah (jaise MySQL start nahi hai) chhoti line mein likhi aati hai. Website live karte waqt `DEBUG = false` kar do.
Testing mein 5 se zyada baar submit karoge to rate limit (15 minute) message aayega, ye normal hai.

## 4. Spam protection
Hidden honeypot field, CSRF token, server-side validation, bohot jaldi submit hone par block, rate limit (har IP se **5 messages / 15 minute**, `config.php` mein badal sakte ho), extra links wale messages block, CSV formula-injection se bachao.

## 5. Apni details daalna
| Kya | Kahan |
|---|---|
| Phone, email, address, company number, registered office, SIC, WhatsApp | `includes/config.php` (`$SITE`), footer, contact section aur emails mein khud lag jata hai |
| Security cards aur blog cards ka text | `includes/content.json` |
| Page ka layout/sections | `index.php` |
| Rang aur fonts | `assets/css/style.css` (sab se upar `:root`) |

## 6. Images aur video lagana
Page par jahan image honi hai wahan dotted **placeholder box** hai jis par file ka naam likha hai.
Apni image **usi naam se** `assets/img/` folder mein rakh do (`.jpg`, `.png` ya `.webp`), code badalne ki zaroorat nahi, wo khud dikh jayegi.

| Page ka hissa | File ka naam |
|---|---|
| Certifications (6 logos) | `cert-1` ... `cert-6` |
| About section ki photo | `about-main` |
| Our Services (3 cards) | `service-security`, `service-cleaning`, `service-training` |
| Trusted by Leading Brands (4 logos) | `brand-1` ... `brand-4` |
| Security Services (4 cards) | `security-1` ... `security-4` |
| Industry Insights (6 cards, auto slider) | `blog-1` ... `blog-6` |

- **Logo**: `assets/img/logo.png` (aapki di hui file, change nahi ki). Header mein sirf CSS se khali kinara chhupa hai.
- **Hero video**: `assets/video/hero.mp4` ko apni video se replace karo (same naam). Abhi wahan ek temporary animated loop hai.
- Certification ke neeche likha text `index.php` mein "Write a short line about this certification..." se badlo.

## 7. Chat button
Neeche-right mein hamesha nazar aane wala button hai ("We are here!"). Click par panel khulta hai: Quote, WhatsApp, Call, Email. WhatsApp ka option tab dikhta hai jab `config.php` mein `whatsapp` number (country code ke sath, bina `+`) daalo.

## 8. Live karne se pehle
- Placeholder phone/email/address replace karo.
- Admin password badlo, `admin/make-hash.php` delete karo.
- Production mein MySQL ka `root` user mat use karo; alag user/password banao aur `config.php` mein daalo.
- Koi accreditation (SIA, ISO, etc.) tab hi dikhao jab company ke paas ho.
