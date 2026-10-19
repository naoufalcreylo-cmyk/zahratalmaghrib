<?php
/**
 * DEVELOPMENT ONLY — seeds a local WordPress Playground with clearly labelled
 * Arabic/French sample content for testing the theme. Never run on production.
 *
 * Category names and slugs mirror the live site's real categories so the
 * homepage sections resolve exactly as they will in production.
 */

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

if ( get_option( 'zam_dev_seeded' ) ) {
	return;
}

$media_dir = '/wordpress/zam-dev/media/';
$note      = '<!-- wp:paragraph {"className":"zam-dev-note"} --><p class="zam-dev-note"><em>محتوى تجريبي لبيئة التطوير فقط — ليس مقالاً منشوراً على زهرة المغرب.</em></p><!-- /wp:paragraph -->';

/* ---------- Users ---------- */
$users = array();
foreach ( array(
	array( 'salma-demo', 'سلمى العلوي', 'صحافية تكتب في قضايا المرأة والأسرة والثقافة. (سيرة تجريبية لبيئة التطوير)' ),
	array( 'younes-demo', 'يونس الإدريسي', '' ),
) as $u ) {
	$id = username_exists( $u[0] );
	if ( ! $id ) {
		$id = wp_insert_user(
			array(
				'user_login'   => $u[0],
				'user_pass'    => wp_generate_password( 24 ),
				'display_name' => $u[1],
				'nickname'     => $u[1],
				'role'         => 'author',
				'description'  => $u[2],
			)
		);
	}
	$users[] = $id;
}

/* ---------- Categories (real names and slugs) ---------- */
$cat_defs = array(
	'news'    => array( 'أخبار وقضايا', 'أخبار-وقضايا', 0 ),
	'beauty'  => array( 'جمال واناقة', 'جمال-واناقة', 0 ),
	'family'  => array( 'أسرة ومجتمع', 'أسرة-ومجتمع', 0 ),
	'culture' => array( 'آداب و فنون', 'آداب-و-فنون', 0 ),
	'sport'   => array( 'رياضة و رشاقة', 'رياضة-و-رشاقة', 0 ),
	'opinion' => array( 'مقالات الرأي', 'مقالات-الرأي', 0 ),
	'stars'   => array( 'نجوم و مشاهير', 'نجوم-و-مشاهير', 0 ),
	'food'    => array( 'شهيوات زهرة', 'شهيوات-زهرة', 0 ),
	'decor'   => array( 'فن و ديكور', 'فن-و-ديكور', 0 ),
	'teens'   => array( 'تحت العشرين', 'تحت-العشرين', 0 ),
	'tv'      => array( 'زهرة TV', 'زهرة-tv', 0 ),
	'fr'      => array( 'En français', 'en-francais', 0 ),
);
$cats = array();
foreach ( $cat_defs as $key => $def ) {
	$t           = wp_insert_term( $def[0], 'category', array( 'slug' => $def[1] ) );
	$cats[ $key ] = is_wp_error( $t ) ? (int) $t->get_error_data( 'term_exists' ) : (int) $t['term_id'];
}
foreach ( array(
	'fr_actus' => array( 'ACTUS/ PEOPLE', 'actus-people' ),
	'fr_edu'   => array( 'EDUCATION/CULTURE', 'educationculture' ),
	'fr_beaute' => array( 'BEAUTE/MODE', 'beautemode' ),
) as $key => $def ) {
	$t           = wp_insert_term( $def[0], 'category', array( 'slug' => $def[1], 'parent' => $cats['fr'] ) );
	$cats[ $key ] = is_wp_error( $t ) ? (int) $t->get_error_data( 'term_exists' ) : (int) $t['term_id'];
}
wp_update_term( $cats['beauty'], 'category', array( 'description' => 'نصائح الجمال والموضة والعناية بالبشرة والشعر. (وصف تجريبي)' ) );
wp_update_term( 1, 'category', array( 'name' => 'ثقافة في صور' ) );

/* ---------- Media ---------- */
function zam_dev_image( $file, $caption = '', $credit = '', $alt = '' ) {
	$tmp = wp_tempnam( $file );
	copy( '/wordpress/zam-dev/media/' . $file, $tmp );
	$id = media_handle_sideload( array( 'name' => $file, 'tmp_name' => $tmp ), 0, $caption ? $caption : null, array( 'post_excerpt' => $caption ) );
	if ( is_wp_error( $id ) ) {
		return 0;
	}
	if ( $alt ) {
		update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	}
	if ( $credit ) {
		$meta                          = wp_get_attachment_metadata( $id );
		$meta['image_meta']['credit'] = $credit;
		wp_update_attachment_metadata( $id, $meta );
	}
	return (int) $id;
}

$img = array();
foreach ( array( 'rose', 'atlas', 'souk', 'tea', 'fashion', 'family', 'books', 'runner', 'kitchen', 'studio', 'garden', 'city', 'desert', 'ocean' ) as $name ) {
	$img[ $name ] = zam_dev_image( $name . '.jpg', 'souk' === $name || 'family' === $name ? 'صورة تجريبية للتوضيح فقط' : '', 'Picsum / Unsplash (sample)', '' );
}
$logo_id = zam_dev_image( 'logozahra.png', '', '', 'زهرة المغرب' );

/* ---------- Helpers ---------- */
function zam_dev_p( $text ) {
	return '<!-- wp:paragraph --><p>' . $text . '</p><!-- /wp:paragraph -->';
}
$lorem = array(
	'في سياق التحولات الاجتماعية التي يعرفها المغرب، تتجدد الأسئلة حول موقع المرأة داخل الأسرة والمجتمع، وحول السبل الكفيلة بتحقيق توازن حقيقي بين الحياة المهنية والحياة الخاصة.',
	'ويرى عدد من المتابعين أن النقاش العمومي اليوم أكثر نضجاً من أي وقت مضى، إذ لم يعد مقتصراً على النخب، بل امتد إلى الفضاءات الرقمية التي فتحت المجال أمام أصوات جديدة.',
	'وتشير المعطيات المتوفرة إلى أن المبادرات المحلية، في المدن كما في القرى، لعبت دوراً مهماً في تقريب الخدمات من المواطنات والمواطنين، وفي خلق دينامية تضامنية لافتة.',
	'ومع ذلك، يبقى الطريق طويلاً أمام تحقيق الأهداف المعلنة، وهو ما يستدعي تضافر جهود المؤسسات والمجتمع المدني ووسائل الإعلام على حد سواء.',
);
function zam_dev_body( $lorem, $note, $n = 3 ) {
	$out = '';
	for ( $i = 0; $i < $n; $i++ ) {
		$out .= zam_dev_p( $lorem[ $i % count( $lorem ) ] );
	}
	return $out . $note;
}
$time = time();
$step = 0;
function zam_dev_post( $args ) {
	global $time, $step, $users;
	$step++;
	$id = wp_insert_post(
		array(
			'post_title'    => $args['title'],
			'post_content'  => $args['content'],
			'post_excerpt'  => $args['excerpt'] ?? '',
			'post_status'   => 'publish',
			'post_author'   => $args['author'] ?? $users[ $step % 2 ],
			'post_category' => (array) $args['cats'],
			'tags_input'    => array_merge( array( 'محتوى تجريبي' ), $args['tags'] ?? array() ),
			'post_date'     => wp_date( 'Y-m-d H:i:s', $time - $step * 9 * HOUR_IN_SECONDS ),
			'meta_input'    => $args['meta'] ?? array(),
		)
	);
	if ( ! empty( $args['image'] ) ) {
		set_post_thumbnail( $id, $args['image'] );
	}
	if ( ! empty( $args['modified'] ) ) {
		global $wpdb;
		$wpdb->update( $wpdb->posts, array( 'post_modified' => current_time( 'mysql' ), 'post_modified_gmt' => current_time( 'mysql', true ) ), array( 'ID' => $id ) );
	}
	return $id;
}

/* ---------- Posts ---------- */
$ids = array();

$ids['lead'] = zam_dev_post(
	array(
		'title'   => 'نساء يقدن التغيير في القرى الجبلية: مبادرات صغيرة تصنع فرقاً كبيراً في حياة الأسر',
		'excerpt' => 'من تعاونيات الأركان إلى أقسام محو الأمية، تروي نساء الأطلس كيف تحولت مبادرات بسيطة إلى مصدر دخل وكرامة.',
		'content' => zam_dev_body( $lorem, $note, 4 ),
		'cats'    => $cats['family'],
		'image'   => $img['atlas'],
		'tags'    => array( 'الرياض' ),
	)
);

$ids['long_headline'] = zam_dev_post(
	array(
		'title'   => '80 مليار دولار حجم النشاط الاقتصادي المرتبط بالبطولة، بمشاركة 8 منتخبات عربية… إنفاق "فيفا" يقفز بأكثر من 121% مقارنة بالنسخة السابقة، و50 مليون دولار جائزة البطل في عنوان طويل جداً لاختبار التخطيط',
		'content' => zam_dev_body( $lorem, $note, 3 ),
		'cats'    => $cats['news'],
		'image'   => $img['city'],
		'tags'    => array( 'الرياض' ),
	)
);

$long_content  = zam_dev_p( 'تعيش الأسرة المغربية اليوم على إيقاع تحولات متسارعة، تمس أنماط العيش والعلاقات بين الأجيال وطرق التربية. في هذا الملف نحاول الإحاطة بأبرز هذه التحولات، انطلاقاً من شهادات وآراء مختصين.' );
$long_content .= '<!-- wp:heading --><h2 class="wp-block-heading">أسرة تتغير بسرعة</h2><!-- /wp:heading -->';
$long_content .= zam_dev_p( $lorem[0] ) . zam_dev_p( $lorem[1] );
$long_content .= '<!-- wp:quote --><blockquote class="wp-block-quote"><!-- wp:paragraph --><p>التربية اليوم لم تعد مسؤولية الأم وحدها، بل مشروع مشترك يتقاسمه الأب والمدرسة والمجتمع.</p><!-- /wp:paragraph --><cite>باحثة في علم الاجتماع (اقتباس تجريبي)</cite></blockquote><!-- /wp:quote -->';
$long_content .= '<!-- wp:heading --><h2 class="wp-block-heading">ما الذي تغير في الأرقام؟</h2><!-- /wp:heading -->';
$long_content .= zam_dev_p( 'تقدّم الأرقام التالية صورة تقريبية فقط، وهي معطيات تجريبية لأغراض العرض ولا تمثل إحصاءات رسمية:' );
$long_content .= '<!-- wp:table {"className":"is-style-stripes"} --><figure class="wp-block-table is-style-stripes"><table><thead><tr><th>المؤشر</th><th>2014</th><th>2024</th></tr></thead><tbody><tr><td>متوسط سن الزواج (تجريبي)</td><td>26</td><td>28</td></tr><tr><td>نسبة الأسر النووية (تجريبي)</td><td>60%</td><td>68%</td></tr><tr><td>عدد الأطفال لكل أسرة (تجريبي)</td><td>2.4</td><td>2.1</td></tr></tbody></table><figcaption class="wp-element-caption">جدول تجريبي للعرض فقط</figcaption></figure><!-- /wp:table -->';
$long_content .= '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">أسباب متعددة</h3><!-- /wp:heading -->';
$long_content .= '<!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li>ارتفاع نسبة تمدرس الفتيات وولوجهن إلى سوق الشغل.</li><!-- /wp:list-item --><!-- wp:list-item --><li>انتقال جزء من الأسر من القرية إلى المدينة.</li><!-- /wp:list-item --><!-- wp:list-item --><li>تأثير وسائل التواصل الاجتماعي على القيم والعادات.</li><!-- /wp:list-item --></ul><!-- /wp:list -->';
$long_content .= '<!-- wp:image {"id":' . $img['family'] . ',"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="' . esc_url( wp_get_attachment_image_url( $img['family'], 'large' ) ) . '" alt="" class="wp-image-' . $img['family'] . '"/><figcaption class="wp-element-caption">صورة تجريبية: لحظة عائلية</figcaption></figure><!-- /wp:image -->';
$long_content .= '<!-- wp:heading --><h2 class="wp-block-heading">نصوص مختلطة واتجاهات متعددة</h2><!-- /wp:heading -->';
$long_content .= zam_dev_p( 'اختبار النصوص المختلطة: نُظّم اللقاء يوم 12 أكتوبر 2026 بمشاركة 150 مشاركة، وقالت إحداهن بالفرنسية: «<span lang="fr" dir="ltr">La famille reste le premier lieu de solidarité</span>». للتواصل: <a href="mailto:contact@example.com">contact@example.com</a> أو عبر الموقع <a href="https://example.org/ar/dossier?id=42">https://example.org/ar/dossier?id=42</a>.' );
$long_content .= '<!-- wp:pullquote --><figure class="wp-block-pullquote"><blockquote><p>الأسرة ليست مؤسسة جامدة، بل كائن حي يتكيف مع زمنه.</p><cite>اقتباس تجريبي</cite></blockquote></figure><!-- /wp:pullquote -->';
$long_content .= '<!-- wp:heading --><h2 class="wp-block-heading">خطوات عملية للأسر</h2><!-- /wp:heading -->';
$long_content .= '<!-- wp:list {"ordered":true} --><ol class="wp-block-list"><!-- wp:list-item --><li>تخصيص وقت يومي للحوار داخل الأسرة.</li><!-- /wp:list-item --><!-- wp:list-item --><li>تقاسم المهام المنزلية بين جميع الأفراد.</li><!-- /wp:list-item --><!-- wp:list-item --><li>مرافقة الأطفال في استعمال الشاشات بدل المنع المطلق.</li><!-- /wp:list-item --></ol><!-- /wp:list -->';
$long_content .= '<!-- wp:gallery {"linkTo":"none","columns":2} --><figure class="wp-block-gallery has-nested-images columns-2 is-cropped"><!-- wp:image {"id":' . $img['garden'] . ',"sizeSlug":"medium_large"} --><figure class="wp-block-image size-medium_large"><img src="' . esc_url( wp_get_attachment_image_url( $img['garden'], 'medium_large' ) ) . '" alt="" class="wp-image-' . $img['garden'] . '"/></figure><!-- /wp:image --><!-- wp:image {"id":' . $img['tea'] . ',"sizeSlug":"medium_large"} --><figure class="wp-block-image size-medium_large"><img src="' . esc_url( wp_get_attachment_image_url( $img['tea'], 'medium_large' ) ) . '" alt="" class="wp-image-' . $img['tea'] . '"/></figure><!-- /wp:image --><figcaption class="blocks-gallery-caption wp-element-caption">معرض صور تجريبي</figcaption></figure><!-- /wp:gallery -->';
$long_content .= '<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->';
$long_content .= zam_dev_p( $lorem[2] ) . zam_dev_p( $lorem[3] ) . $note;

$ids['long'] = zam_dev_post(
	array(
		'title'    => 'الأسرة المغربية في زمن التحولات: ملف خاص عن التربية والعمل وتقاسم الأدوار',
		'excerpt'  => 'ملف تجريبي طويل لاختبار تجربة القراءة: عناوين فرعية، اقتباسات، جداول، قوائم وصور.',
		'content'  => $long_content,
		'cats'     => $cats['family'],
		'image'    => $img['souk'],
		'author'   => $users[0],
		'modified' => true,
		'tags'     => array( 'الرياض', 'التربية' ),
	)
);

$ids['french'] = zam_dev_post(
	array(
		'title'   => 'Le 8 mars, une journée qui peut signifier tout sauf la fête',
		'excerpt' => 'Article d’exemple en français pour tester l’affichage de gauche à droite dans un site arabe.',
		'content' => '<!-- wp:paragraph --><p>Le 8 mars, journée internationale des droits des femmes, est une journée politique avant d’être une célébration. Cet article est un <strong>contenu d’exemple</strong> destiné à l’environnement de développement.</p><!-- /wp:paragraph --><!-- wp:heading --><h2 class="wp-block-heading">Un bilan contrasté</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Malgré des avancées réelles, les inégalités persistent : accès à l’emploi, écarts de salaire, représentation dans les instances de décision. Les chiffres cités ici sont fictifs : 42 % en 2015, 47 % en 2025.</p><!-- /wp:paragraph --><!-- wp:quote --><blockquote class="wp-block-quote"><!-- wp:paragraph --><p>« L’égalité ne se décrète pas, elle se construit au quotidien. »</p><!-- /wp:paragraph --><cite>Citation d’exemple</cite></blockquote><!-- /wp:quote --><!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li>Éducation et formation continue ;</li><!-- /wp:list-item --><!-- wp:list-item --><li>Accès aux soins et à la protection sociale ;</li><!-- /wp:list-item --><!-- wp:list-item --><li>Participation à la vie publique.</li><!-- /wp:list-item --></ul><!-- /wp:list --><!-- wp:paragraph --><p>Un mot en arabe au milieu du texte : <span lang="ar" dir="rtl">زهرة المغرب</span>, puis la suite en français.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p><em>Contenu d’exemple — environnement de développement uniquement.</em></p><!-- /wp:paragraph -->',
		'cats'    => $cats['fr_actus'],
		'image'   => $img['rose'],
	)
);

$ids['fr2'] = zam_dev_post( array( 'title' => 'Éducation : cinq initiatives qui changent l’école au Maroc', 'content' => '<!-- wp:paragraph --><p>Contenu d’exemple en français pour la rubrique Éducation/Culture.</p><!-- /wp:paragraph -->', 'cats' => $cats['fr_edu'], 'image' => $img['books'] ) );
$ids['fr3'] = zam_dev_post( array( 'title' => 'Beauté : les gestes simples pour une peau lumineuse en automne', 'content' => '<!-- wp:paragraph --><p>Contenu d’exemple en français pour la rubrique Beauté/Mode.</p><!-- /wp:paragraph -->', 'cats' => $cats['fr_beaute'] ) );
$ids['fr4'] = zam_dev_post( array( 'title' => 'Portrait : une cheffe marocaine à la conquête de Paris', 'content' => '<!-- wp:paragraph --><p>Contenu d’exemple en français.</p><!-- /wp:paragraph -->', 'cats' => $cats['fr_actus'], 'image' => $img['kitchen'] ) );

$by_cat = array(
	'beauty'  => array(
		array( 'أسرار العناية بالبشرة في فصل الخريف: روتين بسيط بمكونات طبيعية', 'rose' ),
		array( 'القفطان المغربي يعود بقوة إلى منصات الموضة العالمية', 'fashion' ),
		array( 'زيت الأركان: كيف تختارين المنتج الأصلي وتستعملينه بشكل صحيح', 'garden' ),
		array( 'ألوان الموسم: دليل سريع لتنسيق الإطلالة اليومية', '' ),
		array( 'العناية بالشعر بعد الصيف: خمس خطوات أساسية', 'studio' ),
	),
	'family'  => array(
		array( 'كيف نحمي أطفالنا من الإدمان على الشاشات دون صدام؟', 'family' ),
		array( 'العودة إلى المدرسة: نصائح لتنظيم وقت الأسرة', 'books' ),
		array( 'الأمهات العاملات: شهادات عن التوازن الصعب', 'city' ),
		array( 'الجدّات حارسات الذاكرة: حكايات تنتقل بين الأجيال', '' ),
	),
	'stars'   => array(
		array( 'فنانة مغربية تتألق في مهرجان دولي للسينما', 'studio' ),
		array( 'إطلالات النجمات في حفل توزيع الجوائز', 'fashion' ),
		array( 'مصممة أزياء شابة تحمل التراث المغربي إلى العالمية', 'souk' ),
		array( 'حوار مع مطربة صاعدة: الموسيقى رسالة قبل كل شيء', 'ocean' ),
	),
	'culture' => array(
		array( 'معرض الكتاب: إقبال لافت على روايات الكاتبات المغربيات', 'books' ),
		array( 'التشكيل المغربي المعاصر في رواق جديد بالرباط', 'desert' ),
		array( 'قراءة في ديوان شعري جديد عن الذاكرة والمدينة', '' ),
		array( 'المسرح المدرسي: تجربة تستحق التعميم', '' ),
	),
	'sport'   => array(
		array( 'المنتخب النسوي يستعد لاستحقاقات قارية مهمة', 'runner' ),
		array( 'المشي اليومي: فوائد صحية تبدأ من ثلاثين دقيقة', 'garden' ),
		array( 'رياضة اليوغا تستقطب المزيد من المغربيات', '' ),
		array( 'عدّاءة شابة تحطم رقماً وطنياً', 'runner' ),
	),
	'food'    => array(
		array( 'طاجين الدجاج بالبرقوق: وصفة العائلة كما تعدّها الجدات', 'kitchen' ),
		array( 'حلويات مغربية خفيفة لمائدة الشاي', 'tea' ),
		array( 'الحريرة: أسرار النكهة المثالية', '' ),
		array( 'سلطات صيفية منعشة بمكونات من السوق', 'garden' ),
	),
	'opinion' => array(
		array( 'حين تصبح المساواة ممارسة يومية لا شعاراً موسمياً', '' ),
		array( 'الإعلام النسائي ومسؤولية الكلمة', '' ),
		array( 'المدرسة العمومية ورهان تكافؤ الفرص', '' ),
		array( 'هل نحتاج إلى تعريف جديد للنجاح؟', '' ),
	),
	'decor'   => array(
		array( 'الزليج في الديكور العصري: أفكار لإضاءة البيت', 'souk' ),
	),
);
foreach ( $by_cat as $key => $list ) {
	foreach ( $list as $i => $row ) {
		$ids[ $key . $i ] = zam_dev_post(
			array(
				'title'   => $row[0],
				'content' => zam_dev_body( $lorem, $note, 3 ),
				'cats'    => $cats[ $key ],
				'image'   => $row[1] ? $img[ $row[1] ] : 0,
				'author'  => 'opinion' === $key ? $users[ $i % 2 ] : null,
				'tags'    => 0 === $i % 3 ? array( 'الرياض' ) : array(),
			)
		);
	}
}

$news_titles = array(
	'انطلاق البرنامج الوطني للتخييم بمشاركة آلاف الأطفال والشباب',
	'مبادرة جديدة لدعم المقاولات النسائية في الجهات',
	'ندوة بالدار البيضاء حول العنف الرقمي ضد النساء',
	'إطلاق منصة رقمية للتوجيه المدرسي والمهني',
	'حملة تحسيسية للكشف المبكر عن سرطان الثدي',
	'افتتاح مركز جديد لاستقبال النساء في وضعية صعبة',
	'مهرجان للفيلم القصير يحتفي بالمخرجات الشابات',
	'تقرير يرصد تطور تمثيلية النساء في المجالس المنتخبة',
	'قافلة طبية متعددة التخصصات تجوب المناطق الجبلية',
	'برنامج لمحو الأمية الرقمية لفائدة النساء القرويات',
	'جائزة وطنية تكرّم مبادرات التضامن المحلي',
	'لقاء تواصلي حول مدونة الأسرة ومستجداتها',
	'اتفاقية شراكة لتعزيز التكوين المهني للشابات',
	'معرض للصناعة التقليدية بمشاركة تعاونيات نسائية',
	'دراسة حول عادات القراءة لدى الشباب',
	'ورشة لتقوية قدرات الصحافيات في التحقيق',
);
$news_images = array( 'city', '', 'desert', '', 'ocean', 'atlas', 'studio', '', 'garden', 'books', '', 'souk', '', 'tea', 'books', '' );
foreach ( $news_titles as $i => $title ) {
	$ids[ 'news' . $i ] = zam_dev_post(
		array(
			'title'   => $title,
			'content' => zam_dev_body( $lorem, $note, 3 ),
			'cats'    => $cats['news'],
			'image'   => $news_images[ $i ] ? $img[ $news_images[ $i ] ] : 0,
			'tags'    => 0 === $i % 5 ? array( 'الرياض' ) : array(),
		)
	);
}

// The beauty story is the sticky (manual) lead; the rest of the lead block falls back to the latest posts.
stick_post( $ids['beauty1'] );

/* ---------- Comments ---------- */
foreach ( array(
	array( 'قارئة تجريبية', 'ملف مهم جداً، شكراً على هذا العمل. (تعليق تجريبي)' ),
	array( 'Lecteur test', 'Merci pour cet article très complet. (commentaire d’exemple)' ),
) as $c ) {
	wp_insert_comment(
		array(
			'comment_post_ID'  => $ids['long'],
			'comment_author'   => $c[0],
			'comment_content'  => $c[1],
			'comment_approved' => 1,
		)
	);
}

/* ---------- Pages (same titles as the live institutional pages) ---------- */
$pages = array();
foreach ( array(
	'إتصل بنا'  => 'صفحة تجريبية. على الموقع الحقيقي تحتوي هذه الصفحة على عنوان التواصل الرسمي.',
	'للإشهار'   => 'صفحة تجريبية. على الموقع الحقيقي تحتوي هذه الصفحة على معلومات طلبات الإشهار.',
	'فريق العمل' => 'صفحة تجريبية. على الموقع الحقيقي تحتوي هذه الصفحة على أسماء فريق التحرير.',
) as $title => $text ) {
	$pages[] = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_content' => zam_dev_p( $text ),
		)
	);
}
$ad_test = wp_insert_post(
	array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => 'اختبار المساحات الإشهارية (تجريبي)',
		'post_name'    => 'ad-test',
		'post_content' => zam_dev_p( 'صفحة تجريبية لاختبار مساحة إشهارية مملوءة بكود وهمي.' ) . '<!-- wp:zahrat-almaghrib/ad-slot {"format":"leaderboard"} --><!-- wp:html --><div style="width:728px;max-width:100%;height:90px;display:grid;place-items:center;border:1px solid #ccc;font:14px sans-serif">728 × 90 test creative</div><!-- /wp:html --><!-- /wp:zahrat-almaghrib/ad-slot -->' . '<!-- wp:zahrat-almaghrib/ad-slot {"format":"leaderboard"} --><!-- wp:html /--><!-- /wp:zahrat-almaghrib/ad-slot -->' . zam_dev_p( 'المساحة الثانية أعلاه فارغة ويجب ألا تظهر.' ),
	)
);

/* ---------- Footer classic menu ---------- */
$menu_id = wp_create_nav_menu( 'روابط التذييل' );
foreach ( $pages as $page_id ) {
	wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-object-id' => $page_id,
			'menu-item-object'    => 'page',
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		)
	);
}
set_theme_mod( 'nav_menu_locations', array( 'footer' => $menu_id ) );

/* ---------- Primary navigation (block navigation) ---------- */
function zam_dev_nav_link( $label, $cat_id ) {
	return '<!-- wp:navigation-link ' . wp_json_encode(
		array(
			'label' => $label,
			'type'  => 'category',
			'id'    => $cat_id,
			'url'   => get_category_link( $cat_id ),
			'kind'  => 'taxonomy',
		),
		JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
	) . ' /-->';
}
$nav  = '<!-- wp:navigation-link {"label":"الرئيسية","url":"' . home_url( '/' ) . '","kind":"custom"} /-->';
$nav .= zam_dev_nav_link( 'أخبار وقضايا', $cats['news'] );
$nav .= zam_dev_nav_link( 'جمال واناقة', $cats['beauty'] );
$nav .= zam_dev_nav_link( 'أسرة ومجتمع', $cats['family'] );
$nav .= zam_dev_nav_link( 'نجوم و مشاهير', $cats['stars'] );
$nav .= zam_dev_nav_link( 'آداب و فنون', $cats['culture'] );
$nav .= zam_dev_nav_link( 'رياضة و رشاقة', $cats['sport'] );
$nav .= zam_dev_nav_link( 'شهيوات زهرة', $cats['food'] );
$nav .= zam_dev_nav_link( 'مقالات الرأي', $cats['opinion'] );
$nav .= '<!-- wp:navigation-submenu {"label":"المزيد","url":"#","kind":"custom"} -->';
$nav .= zam_dev_nav_link( 'فن و ديكور', $cats['decor'] );
$nav .= zam_dev_nav_link( 'تحت العشرين', $cats['teens'] );
$nav .= zam_dev_nav_link( 'زهرة TV', $cats['tv'] );
$nav .= '<!-- /wp:navigation-submenu -->';
$nav .= zam_dev_nav_link( 'En français', $cats['fr'] );
wp_insert_post(
	array(
		'post_type'    => 'wp_navigation',
		'post_status'  => 'publish',
		'post_title'   => 'القائمة الرئيسية',
		'post_content' => $nav,
	)
);

/* ---------- Logo + editors' picks (dev mu-plugin reads this option) ---------- */
if ( $logo_id ) {
	set_theme_mod( 'custom_logo', $logo_id );
}
update_option( 'zam_dev_picks', array( $ids['culture2'], $ids['news3'], $ids['beauty3'], $ids['family3'] ) );
update_option( 'zam_dev_ids', $ids );
update_option( 'zam_dev_seeded', 1 );
