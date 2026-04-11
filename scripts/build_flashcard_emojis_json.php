<?php

/**
 * Regenerates resources/data/flashcard_emojis.json from curated category lists.
 * Run: php scripts/build_flashcard_emojis_json.php
 */

declare(strict_types=1);

$base = dirname(__DIR__);

$categories = [
    'Smileys & emotion' => '😀😃😄😁😆😅🤣😂🙂🙃😉😊😇🥰😍🤩😘😗☺️😚😙🥲😋😛😜🤪😝🤑🤗🤭🫢🫣🤫🤔🫡🤐🤨😐😑😶‍🌫️😶😏😒🙄😬😮‍💨🤥😌😔😪🤤😴😷🤒🤕🤢🤮🤧🥵🥶🥴😵‍💫😵🤯🤠🥳🥸😎🤓🧐😕😟🙁☹️😮😯😲😳🥺🥹😦😧😨😰😥😢😭😱😖😣😞😓😩😫🥱😤😡😠🤬😈👿💀☠️💩🤡👹👺👻👽👾🤖😺😸😹😻😼😽🙀😿😾',
    'Hearts & symbols' => '❤️🧡💛💚💙💜🖤🤍🤎💔❤️‍🔥❤️‍🩹💕💞💓💗💖💘💝💟♥️💌💯✨💫⭐🌟✴️❇️🔅🔆〽️⚜️✅❌❓❔❕❗💢💬👁️‍🗨️🗨️🗯️💭💤',
    'Hands & gestures' => '👋🤚🖐️✋🖖👌🤌🤏✌️🤞🫰🤟🤘🤙👈👉👆🖕👇☝️🫵👍👎✊👊🤛🤜👏🙌🫶👐🤲🤝🙏✍️💅🤳🫳🫴🫷🫸',
    'People & family' => '👶🧒👦👧🧑👱👨👩🧓👴👵👮‍♂️👮‍♀️🕵️‍♂️🕵️‍♀️💂‍♂️💂‍♀️👷‍♂️👷‍♀️🤴👸👳‍♂️👳‍♀️👲🧕🤵‍♂️👰‍♀️🤰🤱👼🎅🧙‍♂️🧙‍♀️🧚‍♂️🧚‍♀️🧛‍♂️🧛‍♀️🧜‍♂️🧜‍♀️🧝‍♂️🧝‍♀️🧞‍♂️🧞‍♀️🕺💃🕴️👯‍♂️👯‍♀️🧖‍♂️🧖‍♀️🧗‍♂️🧗‍♀️🤹‍♂️🤹‍♀️🛀🛌🧑‍🤝‍🧑👭👫👬👨‍👩‍👧‍👦👨‍👩‍👧👨‍👩‍👦👩‍👩‍👧‍👦',
    'Skin tones (hands)' => '👍🏻👍🏼👍🏽👍🏾👍🏿👎🏻👎🏼👎🏽👎🏾👎🏿✌🏻✌🏼✌🏽✌🏾✌🏿🙌🏻🙌🏼🙌🏽🙌🏾🙌🏿👏🏻👏🏼👏🏽👏🏾👏🏿🙏🏻🙏🏼🙏🏽🙏🏾🙏🏿👋🏻👋🏼👋🏽👋🏾👋🏿',
    'Animals & nature' => '🐶🐱🐭🐹🐰🦊🐻🐼🐨🐯🦁🐮🐷🐽🐸🐵🙈🙉🙊🐒🐔🐧🐦🐤🐣🐥🦆🦅🦉🦇🐺🐗🐴🦄🐝🐛🦋🐌🐞🐜🦟🦗🕷️🦂🐢🐍🦎🦖🦕🐙🦑🦐🦞🦀🐡🐠🐟🐬🐳🐋🦈🐊🦓🦍🦧🐘🦛🦏🐪🐫🦒🦘🦬🐄🐎🐖🐏🦙🐐🦌🐕‍🦺🦮🐕🐩🐈‍⬛🐈🐓🦃🦚🦜🦢🕊️🐇🦝🦨🦡🦫🦦🦥🦔🐁🐀🦅🦆🦢',
    'Plants & food' => '🌸💮🏵️🌹🥀🌺🌻🌼🌷🪻🪷🌱🪴🌲🌳🌴🌵🌾🌿☘️🍀🍁🍂🍃🍇🍈🍉🍊🍋🍌🍍🥭🍎🍏🍐🍑🍒🍓🫐🥝🍅🫒🥥🥑🍆🥔🥕🌽🌶️🫑🥒🥬🥦🧄🧅🍄‍🟫🍄🫘🌰🍞🥐🥖🫓🥨🥯🥞🧇🧈🍳🥚🧀🍗🍖🦴🌭🍔🍟🍕🫔🥪🥙🧆🌮🌯🥗🥘🫕🥫🍝🍜🍲🍛🍣🍱🥟🦪🍤🍙🍚🍘🍥🥠🥮🍢🍡🍧🍨🍦🥧🧁🍰🎂🍮🍭🍬🍫🍿🍩🍪🥜🍯🥛🍼☕🫖🍵🧃🥤🧋🧉🍶🍺🍻🥂🍷🥃🍸🍹🧊',
    'Sports & games' => '⚽🏀🏈⚾🥎🎾🏐🏉🥏🎱🏓🏸🏒🏑🥍🏏🪃🥅⛳🪁🛝🏹🎣🤿🥊🥋🎽🛹🛼🛷⛸️🥌🎿⛷️🏂🪂🏋️‍♂️🏋️‍♀️🤸‍♂️🤸‍♀️⛹️‍♂️⛹️‍♀️🤺🤾‍♂️🤾‍♀️🏌️‍♂️🏌️‍♀️🏇🧘‍♂️🧘‍♀️🏄‍♂️🏄‍♀️🚴‍♂️🚴‍♀️🏊‍♂️🏊‍♀️🤽‍♂️🤽‍♀️🚣‍♂️🚣‍♀️🧗‍♂️🧗‍♀️🎮🕹️🎲♟️🎯🎳🎰',
    'Music & arts' => '🎨🖌️🖍️🎭🩰🎪🎤🎧🎼🎹🥁🎷🎺🎸🪕🎻🪘🎵🎶🎙️📻🎬🎞️📽️🖼️🧵🪡🧶🪩',
    'Books & school' => '📔📕📗📘📙📚📓📒📃📜📰🗞️📑🔖🏷️💼🎒✏️✒️🖋️🖊️🖍️📐📏📌📎✂️🗃️🗂️📅📆🗓️📈📉📊📋📍🖇️🧮📖🔍🔎💡🔦🏫🎓🧑‍🏫🧑‍🎓🧒✍️📝',
    'Travel & places' => '🚗🚕🚙🚌🚎🏎️🚓🚑🚒🚐🛻🚚🚛🚜🏍️🛵🚲🛴🛹🛼🚁🛸✈️🛫🛬🪂💺🚀🛰️🚉🚊🚝🚞🚂🚆🚇🚈🚋🚃🚟🚠🚡⛴️🛳️⛵🛶🚤🛥️⚓🪝⛽🚨🚥🚦🛑🚧🗼🗽🏰🏯🏟️🎡🎢🎠⛲⛱️🏖️🏝️🏔️⛰️🌋🗻🏕️🏞️🛣️🛤️🌉🌁🌆🏙️🌃🌇',
    'Home & objects' => '🏠🏡🏘️🏚️🏢🏣🏤🏥🏦🏨🏩🏪🏫🏬🏭💒🛋️🛏️🛌🪑🚪🪟🛁🧴🪒🧷🧹🧺🧻🚽🚿🛀🧼🪥🧽🪣🔑🗝️🛎️🖼️🛍️🎁🎈🎏🎀🎊🎉🎎🏮🪔📱📲☎️📞📟📠🔋🪫💻🖥️🖨️⌨️🖱️🖲️💽💾💿📀📼📷📸📹🎥',
    'Clothing & style' => '👓🕶️🥽🥼🦺👔👕👖🧣🧤🧥🧦👗👘🥻🩱🩲🩳👙👚👛👜👝🎒🩴👞👟🥾🥿👠👡🩰👢🪖⛑️👒🎩🎓🧢🪶📿💄💍💎',
    'Weather & sky' => '🌍🌎🌏🌐🌑🌒🌓🌔🌕🌖🌗🌘🌙🌚🌛🌜☀️🌝🌞⭐🌟🌠☁️⛅🌤️🌥️🌦️🌧️⛈️🌩️🌨️❄️☃️⛄🌬️💨💧💦☔☂️🌈🌊🔥⚡🌪️🌫️',
    'Shapes & colors' => '🔴🟠🟡🟢🔵🟣🟤⚫⚪🟥🟧🟨🟩🟦🟪🟫⬛⬜◼️◻️◾◽▪️▫️🔶🔷🔸🔹🔺🔻💠🔘🔳🔲',
    'Numbers' => '0️⃣1️⃣2️⃣3️⃣4️⃣5️⃣6️⃣7️⃣8️⃣9️⃣🔟#️⃣*️⃣🔢',
    'Celebration & awards' => '🎉🎊🎈🎁🏆🥇🥈🥉🎖️🎗️🏅🎆🎇🧨🎂🍰🥳🪅🪆',
    'Africa & heritage' => '🌍🏺🪘🥁🎶👑🌾🛖🦁🐘🦓🦒🏜️🌅🌄🧺🪔🕯️📿🤝👐🙌✊✌️🫶🦅🦛🦏🐊🦍🌴🥥🍌🏞️',
    'Flags (sample)' => '🇺🇬🇰🇪🇹🇿🇷🇼🇿🇦🇳🇬🇬🇭🇪🇹🇸🇴🇺🇸🇬🇧🇫🇷🇩🇪🇪🇸🇮🇹🇯🇵🇰🇷🇨🇳🇧🇷🇲🇽🇨🇦🇦🇺',
];

$splitGraphemes = static function (string $blob): array {
    // PCRE \X = extended grapheme cluster (works without ext-intl)
    if (preg_match_all('/\X/u', $blob, $m)) {
        return array_values(array_filter($m[0], fn ($g) => $g !== ''));
    }

    return [];
};

$out = [];
foreach ($categories as $label => $blob) {
    $merged = $splitGraphemes($blob);
    $out[$label] = array_values(array_unique(array_filter($merged, fn ($g) => $g !== '')));
}

$target = $base.'/resources/data/flashcard_emojis.json';
if (! is_dir(dirname($target))) {
    mkdir(dirname($target), 0755, true);
}

file_put_contents(
    $target,
    json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n"
);

$total = array_sum(array_map('count', $out));
fwrite(STDERR, "Wrote {$target} ({$total} emojis in ".count($out)." categories).\n");
