<?php
/**
 * Cleaning dropdown ke 4 pages ka content (format security_pages.php jaisa).
 * Images abhi placeholder box hain: assets/img/ mein isi naam se .jpg/.png/.webp rakh do.
 */
return [

    'commercial-cleaning' => [
        'file'  => 'commercial-cleaning.php',
        'nav'   => 'Commercial Cleaning',
        'h1'    => 'Commercial Cleaning',
        'title' => 'Commercial Cleaning',
        'desc'  => 'Reliable commercial cleaning for offices, shops and business premises, planned around your industry and your working hours.',
        'sections' => [
            ['type' => 'row', 'img' => 'commercial-cleaning-1', 'imglabel' => 'Commercial cleaning image', 'imgalt' => 'Cleaner with a trolley in a clean corridor',
             'heading' => 'A clean workplace leaves a lasting impression',
             'paras' => [
                'A clean, well-kept workspace impresses your clients and supports a healthier place to work for your team.',
                'Our commercial cleaning service keeps your premises in top condition and shows the professionalism and attention to detail your business stands for.',
                'Reach out to SK Facilities Group and see the difference a dependable cleaning partner makes.',
             ]],
            ['type' => 'grid', 'heading' => 'Why choose our commercial cleaning',
             'items' => [
                ['Solutions for your industry', 'Every sector has its own cleaning needs. We cover shops, offices, warehouses and other commercial premises.'],
                ['Modern techniques', 'Up-to-date methods and equipment get the job done efficiently, without getting in the way of your business.'],
                ['Safety first', 'Our cleaners follow safety procedures, so everyone on your premises stays safe.'],
                ['Consistent quality', 'Regular inspections and feedback keep our standards high, visit after visit.'],
             ]],
        ],
    ],

    'domestic-cleaning' => [
        'file'  => 'domestic-cleaning.php',
        'nav'   => 'Domestic Cleaning',
        'h1'    => 'Domestic Cleaning',
        'title' => 'Domestic Cleaning',
        'desc'  => 'Home cleaning tailored to your household, from light regular cleans to deep cleans, on a schedule that suits you.',
        'sections' => [
            ['type' => 'row', 'img' => 'domestic-cleaning-1', 'imglabel' => 'Domestic cleaning image', 'imgalt' => 'Cleaning team working together in a kitchen',
             'heading' => 'Your home, looked after',
             'paras' => [
                'Your home is your sanctuary, and every household has its own way of living. Our domestic cleaning is shaped around what you need, so you come home to a fresh, sparkling space.',
             ]],
            ['type' => 'grid', 'heading' => 'Our promise',
             'items' => [
                ['Customised cleaning', 'From a light tidy to a deep clean, we work to your list and aim to exceed your expectations.'],
                ['Trained professionals', 'Our cleaners are trained to a high standard and respect your privacy and your home.'],
                ['Gentle products', 'We use non-toxic, environmentally friendly products where we can, to look after your family and the planet.'],
                ['Flexible scheduling', 'Daily, weekly or monthly, we fit around your routine.'],
             ]],
        ],
    ],

    'high-rise-cleaning' => [
        'file'  => 'high-rise-cleaning.php',
        'nav'   => 'High Rise Cleaning',
        'h1'    => 'High Rise Cleaning Services',
        'title' => 'High Rise Cleaning Services',
        'desc'  => 'Specialist cleaning for tall buildings: windows, façades, common areas and more, scheduled to cause as little disruption as possible.',
        'sections' => [
            ['type' => 'row', 'img' => 'high-rise-cleaning-1', 'imglabel' => 'High rise cleaning image 1', 'imgalt' => 'Cleaners on ropes washing the glass of a tall building',
             'heading' => 'Raise the standard of cleanliness',
             'paras' => [
                'Tall buildings bring their own challenges when it comes to cleaning and maintenance. Our dedicated team is trained and equipped to keep your building looking good, inside and out.',
             ]],
            ['type' => 'grid', 'heading' => 'Why choose our high rise cleaning',
             'items' => [
                ['Safety first', 'Working at height needs care. Our team follows strict safety procedures on every job.'],
                ['Specialist training', 'Our crew is trained in high rise cleaning techniques, not just general cleaning.'],
                ['Modern equipment', 'Quality equipment and environmentally friendly solutions give a spotless result.'],
                ['Flexible schedules', 'We plan around the way your building runs, to keep disruption to a minimum.'],
             ]],
            ['type' => 'row', 'flip' => true, 'img' => 'high-rise-cleaning-2', 'imglabel' => 'High rise cleaning image 2', 'imgalt' => 'Window cleaner on ropes using a long-handled brush',
             'heading' => 'A complete high rise solution',
             'list' => [
                'External and internal window cleaning',
                'Building façade and cladding cleaning',
                'Pressure washing',
                'Gutter cleaning and maintenance',
                'Lift and escalator cleaning',
                'Common area cleaning and maintenance',
             ],
             'paras' => [
                'From window washing to façade cleaning and interior upkeep, we put together a service that fits your building.',
                'Whether you need a one-off deep clean or regular maintenance, we are here to keep your property in excellent condition.',
             ]],
        ],
    ],

    'after-build-cleaning' => [
        'file'  => 'after-build-cleaning.php',
        'nav'   => 'After Build Cleaning',
        'h1'    => 'One-Off After Build Cleaning',
        'title' => 'One-Off After Build Cleaning Services',
        'desc'  => 'A thorough one-off clean after construction or renovation work, removing dust, debris and residue so your space is ready to use.',
        'sections' => [
            ['type' => 'row', 'img' => 'after-build-cleaning-1', 'imglabel' => 'After build cleaning image 1', 'imgalt' => 'Cleaner mopping a newly finished interior',
             'heading' => 'The finishing touch after the builders leave',
             'paras' => [
                'A construction or renovation project can transform a space, but it usually leaves a layer of dust and debris behind.',
                'Our one-off after build cleaning service makes sure your new or renovated space looks just the way you pictured it.',
             ]],
            ['type' => 'grid', 'heading' => 'Why choose our after build cleaning',
             'items' => [
                ['Expertise', 'Our team knows where building dust likes to hide and has the tools to clear it.'],
                ['Safety first', 'Post-construction sites can have hazards. We are trained to work on them safely.'],
                ['Tailored service', 'Every project is different, so we talk to you first and shape the clean around it.'],
                ['Modern equipment', 'The right equipment lets us deal with even the toughest post-build mess.'],
                ['Eco-friendly products', 'We use environmentally friendly products where we can, without losing quality.'],
             ]],
            ['type' => 'row', 'flip' => true, 'img' => 'after-build-cleaning-2', 'imglabel' => 'After build cleaning image 2', 'imgalt' => 'Operative sweeping up dust after building work',
             'heading' => 'What is included',
             'list' => [
                'Dusting and wiping down all surfaces',
                'Vacuuming and mopping floors',
                'Cleaning the inside of windows and frames',
                'Removal of paint and plaster marks',
                'Cleaning doors, switches and sockets',
                'Full bathroom and kitchen clean (where applicable)',
             ],
             'paras' => [
                'An after build clean does more than make a space look good. It removes dust and residues that can be harmful, so the area is comfortable and safe to occupy.',
                'We keep our prices competitive and work efficiently, so your space is ready for use as quickly as possible.',
             ]],
        ],
    ],

];
