<?php
declare(strict_types=1);

/*
 * SK Facilities - chat assistant (rule based, AI nahi).
 * Visitor ke har message ka foran jawab deta hai. Jawab config.php ($SITE) aur content.json se bante hain,
 * isliye phone/email/hours badalne par bot khud update ho jata hai.
 *
 * sk_bot_reply($text, $SITE, $teamOnline) => ['text' => string, 'chips' => [ ['label'=>..,'text'=>..] | ['label'=>..,'href'=>..] ]]
 */

/** Chips jo har jagah dikhte hain (main menu) */
function sk_bot_menu(): array
{
    return [
        ['label' => 'Our services',     'text' => 'What services do you offer?'],
        ['label' => 'Get a quote',      'text' => 'I would like a quote'],
        ['label' => 'Contact details',  'text' => 'How can I contact you?'],
        ['label' => 'Talk to a person', 'text' => 'I want to talk to a person'],
    ];
}

function sk_bot_has(string $t, array $words): bool
{
    foreach ($words as $w) {
        // Chhote lafz (hi, job) poore lafz ki tarah match hon, lambe lafz ka hissa bhi chalega
        if (mb_strlen($w) <= 3 ? preg_match('/\b' . preg_quote($w, '/') . '\b/u', $t) : mb_strpos($t, $w) !== false) return true;
    }
    return false;
}

function sk_bot_reply(string $raw, array $SITE, bool $teamOnline): array
{
    $t   = mb_strtolower(trim($raw));
    $tel = preg_replace('/[^+\d]/', '', $SITE['phone']);
    $wa  = (preg_match('/^\d{8,15}$/', (string)$SITE['whatsapp']) && $SITE['whatsapp'] !== '000000000000') ? (string)$SITE['whatsapp'] : '';

    $contactChips = [['label' => 'Call us', 'href' => 'tel:' . $tel], ['label' => 'Email us', 'href' => 'mailto:' . $SITE['email']]];
    if ($wa) $contactChips[] = ['label' => 'WhatsApp', 'href' => 'https://wa.me/' . $wa . '?text=' . rawurlencode('Hello, I would like to know more about your services.')];
    $quoteChip = ['label' => 'Get a quote', 'href' => '#quote'];

    // 1) Visitor ne email / phone chhoda
    if (preg_match('/[^\s@]+@[^\s@]+\.[^\s@]{2,}/', $t) || preg_match('/(?:\+|00)?\d[\d\s().-]{7,}\d/', $t)) {
        return ['text' => "Thank you, we have your contact details. A member of our team will get back to you"
            . ($teamOnline ? ' very shortly.' : ' as soon as we are back (' . $SITE['hours'] . ').')
            . "\n\nIs there anything else I can help with?", 'chips' => [$quoteChip, sk_bot_menu()[0]]];
    }

    // 2) Insaan se baat
    if (sk_bot_has($t, ['talk to a person', 'talk to someone', 'speak to', 'human', 'agent', 'real person', 'representative', 'operator', 'someone'])) {
        if ($teamOnline) {
            return ['text' => "Of course. I have let our team know and someone will reply here in a moment.\n\nIn the meantime, you can tell me what you need help with.", 'chips' => []];
        }
        return ['text' => "Our team is not on chat right now (" . $SITE['hours'] . "), but your message is saved and they will reply as soon as they can.\n\nPlease leave your name and an email or phone number here so we can reach you. For anything urgent, call " . $SITE['phone'] . '.', 'chips' => $contactChips];
    }

    // 3) Salam / hello
    if (preg_match('/^(hi|hello|hey|hiya|yo|salam|assalam|aoa|good (morning|afternoon|evening))\b/u', $t) && mb_strlen($t) < 40) {
        return ['text' => "Hello! 👋 Welcome to " . $SITE['name'] . ". I can tell you about our services, help you get a quote, or put you in touch with the team.\n\nWhat would you like to know?", 'chips' => sk_bot_menu()];
    }

    // 4) Shukriya / bye
    if (sk_bot_has($t, ['thank', 'thanks', 'thx', 'shukriya', 'cheers', 'great', 'perfect']) && mb_strlen($t) < 50) {
        return ['text' => "You are welcome! If you need anything else, just type here any time.", 'chips' => sk_bot_menu()];
    }
    if (preg_match('/^(bye|goodbye|see you|that.?s all|no thanks|nothing)\b/u', $t)) {
        return ['text' => "Thanks for chatting with us. Have a great day!", 'chips' => []];
    }

    // 5) Quote / qeemat
    if (sk_bot_has($t, ['quote', 'price', 'pricing', 'cost', 'how much', 'rates', 'estimate', 'charge', 'afford', 'cheap'])) {
        return ['text' => "Our prices depend on the site, the hours and the type of service, so we give a free, no-obligation quote for each job.\n\nThe quickest way is the \"Get a Quick Quote\" form on this page. Tell us what you need and we will come back to you, or you can call " . $SITE['phone'] . '.', 'chips' => [$quoteChip, ['label' => 'Contact details', 'text' => 'How can I contact you?']]];
    }

    // 6) Services
    if (sk_bot_has($t, ['guard', 'guarding', 'manned', 'door supervisor', 'bouncer', 'security officer'])) {
        return ['text' => "Our Manned Guarding provides professional security officers for your premises, events or construction site, on a short or long-term basis.", 'chips' => [$quoteChip, ['label' => 'Other security services', 'text' => 'What security services do you offer?']]];
    }
    if (sk_bot_has($t, ['patrol', 'mobile patrol'])) {
        return ['text' => "Our Mobile Patrol team visits your site at set or random times to check doors, gates and alarms, and reports back to you.", 'chips' => [$quoteChip, ['label' => 'Other security services', 'text' => 'What security services do you offer?']]];
    }
    if (sk_bot_has($t, ['cctv', 'camera', 'surveillance'])) {
        return ['text' => "We design and install CCTV systems so you can see what is happening at your premises and keep a record when you need it.", 'chips' => [$quoteChip, ['label' => 'Other security services', 'text' => 'What security services do you offer?']]];
    }
    if (sk_bot_has($t, ['alarm', 'keyhold', 'key hold', 'key holding', 'call out', 'callout'])) {
        return ['text' => "With Alarm Response & Keyholding, we hold your keys and attend when your alarm goes off, so you do not have to get out of bed at 3am.", 'chips' => [$quoteChip, ['label' => 'Other security services', 'text' => 'What security services do you offer?']]];
    }
    if (sk_bot_has($t, ['security'])) {
        return ['text' => "Our security services are:\n• Manned Guarding\n• Mobile Patrol\n• CCTV Installation\n• Alarm Response & Keyholding\n\nTell me which one you are interested in, or ask for a quote.", 'chips' => [
            ['label' => 'Manned guarding', 'text' => 'Tell me about manned guarding'], ['label' => 'Mobile patrol', 'text' => 'Tell me about mobile patrol'],
            ['label' => 'CCTV', 'text' => 'Tell me about CCTV'], $quoteChip]];
    }
    if (sk_bot_has($t, ['clean', 'cleaning', 'janitor', 'domestic', 'high rise', 'after build', 'office clean'])) {
        return ['text' => "Our Cleaning Services cover commercial, domestic, high-rise and after-build cleaning. We can set up a regular schedule or do a one-off clean.", 'chips' => [$quoteChip, ['label' => 'Other services', 'text' => 'What services do you offer?']]];
    }
    if (sk_bot_has($t, ['garden', 'grounds', 'horticulture', 'landscap', 'lawn', 'mow', 'hedge', 'grass', 'tree'])) {
        return ['text' => "Our Horticulture team looks after your outdoor spaces: grounds maintenance, mowing, edging, weeding and keeping the site tidy all year round.", 'chips' => [$quoteChip, ['label' => 'Other services', 'text' => 'What services do you offer?']]];
    }
    if (sk_bot_has($t, ['training', 'course', 'learn', 'sia ', 'licence', 'license', 'qualification'])) {
        return ['text' => "We offer Training Courses for security and facilities staff. Send us a few details through the quote form or leave your email here and the team will share the course details and dates.", 'chips' => [$quoteChip, ['label' => 'Other services', 'text' => 'What services do you offer?']]];
    }
    if (sk_bot_has($t, ['service', 'offer', 'tell me more', 'what do you do', 'what can you', 'help with', 'products'])) {
        return ['text' => "We help businesses with:\n• Security Services (guarding, patrols, CCTV, alarm response)\n• Cleaning Services\n• Horticulture Services\n• Training Courses\n\nWhich one would you like to know more about?", 'chips' => [
            ['label' => 'Security', 'text' => 'What security services do you offer?'], ['label' => 'Cleaning', 'text' => 'Tell me about cleaning'],
            ['label' => 'Horticulture', 'text' => 'Tell me about horticulture'], ['label' => 'Training', 'text' => 'Tell me about training courses']]];
    }

    // 7) Contact, waqt, jagah, naukri
    if (sk_bot_has($t, ['contact', 'phone', 'call', 'email', 'mail', 'number', 'whatsapp', 'reach'])) {
        return ['text' => "You can reach us here:\n📞 " . $SITE['phone'] . "\n✉️ " . $SITE['email'] . "\n🕘 " . $SITE['hours'], 'chips' => $contactChips];
    }
    if (sk_bot_has($t, ['hours', 'open', 'opening', 'closed', 'when', 'available', 'weekend', 'sunday', 'saturday'])) {
        return ['text' => "Our office hours are " . $SITE['hours'] . ". You can leave a message here at any time and we will reply as soon as we can.", 'chips' => [$quoteChip, ['label' => 'Contact details', 'text' => 'How can I contact you?']]];
    }
    if (sk_bot_has($t, ['where', 'address', 'location', 'office', 'based', 'area', 'cover', 'near', 'visit', 'belfast', 'derry', 'londonderry', 'ireland'])) {
        return ['text' => "We are based at " . implode(', ', $SITE['address']) . ".\n\nTell us where your site is and we will let you know if we can cover it.", 'chips' => [$quoteChip, ['label' => 'Contact details', 'text' => 'How can I contact you?']]];
    }
    if (sk_bot_has($t, ['job', 'jobs', 'career', 'vacancy', 'vacancies', 'hiring', 'recruit', 'apply', 'work for', 'employment'])) {
        return ['text' => "Thanks for your interest in working with us! Please send your CV and the role you are looking for to " . $SITE['email'] . ", or leave your name and contact details here and the team will get back to you.", 'chips' => [['label' => 'Email us', 'href' => 'mailto:' . $SITE['email']], sk_bot_menu()[0]]];
    }

    // 8) "I have a question" chip
    if (sk_bot_has($t, ['i have a question', 'question', 'ask', 'enquiry', 'inquiry', 'help'])) {
        return ['text' => "Sure, go ahead and type your question. I can help with our services, quotes, opening hours and contact details, or I can pass it to the team.", 'chips' => sk_bot_menu()];
    }

    // 9) Samajh nahi aaya
    return ['text' => "Thanks for your message, I have passed it to our team. " . ($teamOnline ? 'Someone will reply here shortly.' : 'They will reply as soon as they can (' . $SITE['hours'] . ').')
        . "\n\nIf you leave your email or phone number here, they can reach you even if you close this chat. Or pick one of these:", 'chips' => sk_bot_menu()];
}
