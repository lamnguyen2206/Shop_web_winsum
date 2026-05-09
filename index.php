<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $view = isset($_GET['view']) ? (string) $_GET['view'] : 'home';
    $pageTitle = 'Winsum Home | Nội thất và chiếu sáng cao cấp';
    if ($view === 'catalog') {
        $pageTitle = 'Sản phẩm | Winsum Home';
    } elseif ($view === 'product') {
        $pageTitle = 'Chi tiết sản phẩm | Winsum Home';
    } elseif ($view === 'blog') {
        $pageTitle = 'Tin tức | Winsum Home';
    } elseif ($view === 'post') {
        $pageTitle = 'Chi tiết bài viết | Winsum Home';
    } elseif ($view === 'blog-editor') {
        $pageTitle = 'Soạn bài blog | Winsum Home';
    } elseif ($view === 'cart') {
        $pageTitle = 'Giỏ hàng | Winsum Home';
    } elseif ($view === 'checkout') {
        $pageTitle = 'Thanh toán | Winsum Home';
    } elseif ($view === 'account') {
        $pageTitle = 'Tài khoản | Winsum Home';
    } elseif ($view === 'orders') {
        $pageTitle = 'Đơn hàng của tôi | Winsum Home';
    } elseif ($view === 'order-detail') {
        $pageTitle = 'Chi tiết đơn hàng | Winsum Home';
    }
    ?>
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/blog.css">
    <link rel="stylesheet" href="assets/css/cart.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
    <link rel="stylesheet" href="assets/css/home.css">
    <link rel="stylesheet" href="assets/css/catalog.css">
    <link rel="stylesheet" href="assets/css/cart.css">
    <link rel="stylesheet" href="assets/css/account.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
<section class="hero-slider">
    <div class="slide">
        <img src="assets/images/index-banner1.jpg" alt="">
    </div>
    <div class="slide">
        <img src="assets/images/index-banner2.webp" alt="">
    </div>

</section>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
<script>
    $('.hero-slider').slick({
        autoplay: true,
        autoplaySpeed: 5000,
        arrows: true,
        dots: true,
        fade: true,
        prevArrow:
        `<button class="prev">
            &#10094;
        </button>`,
    <main>
        <?php
        if ($view === 'home') {
            include 'includes/home.php';
        } elseif ($view === 'catalog') {
            include 'includes/catalog.php';
        } elseif ($view === 'product') {
            include 'includes/product-detail.php';
        } elseif ($view === 'blog') {
            include 'includes/blog.php';
        } elseif ($view === 'post') {
            include 'includes/blog-detail.php';
        } elseif ($view === 'blog-editor') {
            include 'includes/blog-editor.php';
        } elseif ($view === 'cart') {
            include 'includes/cart.php';
        } elseif ($view === 'checkout') {
            include 'includes/checkout.php';
        } elseif ($view === 'account') {
            include 'includes/account.php';
        } elseif ($view === 'orders') {
            include 'includes/my-orders.php';
        } elseif ($view === 'order-detail') {
            include 'includes/order-detail.php';
        } else {
            include 'includes/home.php';
        }
        ?>
    </main>

        nextArrow:
        `<button class="next">
            &#10095;
        </button>`
    });
</script>
<section class="section awe-section-15">	
	<div class="ega-policies">
	<div class="container">
		<div class="policies-body">							
			<div class="policies-item text-center">
				<div class="policies-image">
					<img loading=lazy src="//bizweb.dktcdn.net/100/514/823/themes/995743/assets/policies_icon_1.png?1759032268598" alt="policies_icon_1.png" 
						 width="40"
						 height="40"
						 class="img-fluid">
				</div>
				<div class="policies-info">
					<h3 class="policies-title">Giao hàng hỏa tốc</h3>
					<div class="policies-desc">Nhận hàng trong vòng 24h</div>
				</div>
			</div>
																				
			<div class="policies-item text-center">
				<div class="policies-image">
					<img loading=lazy src="//bizweb.dktcdn.net/100/514/823/themes/995743/assets/policies_icon_2.png?1759032268598" alt="policies_icon_2.png" 
						 width="40"
						 height="40"
						 class="img-fluid">
				</div>
				<div class="policies-info">
					<h3 class="policies-title">Quà tặng hấp dẫn</h3>
					<div class="policies-desc">Nhiều ưu đãi khuyến mãi hot</div>
				</div>
			</div>
																				
			<div class="policies-item text-center">
				<div class="policies-image">
					<img loading=lazy src="//bizweb.dktcdn.net/100/514/823/themes/995743/assets/policies_icon_3.png?1759032268598" alt="policies_icon_3.png" 
						 width="40"
						 height="40"
						 class="img-fluid">
				</div>
				<div class="policies-info">
					<h3 class="policies-title">Bảo đảm chất lượng</h3>
					<div class="policies-desc">Sản phẩm đã dược kiểm định</div>
				</div>
			</div>
																				
			<div class="policies-item text-center">
				<div class="policies-image">
					<img loading=lazy src="//bizweb.dktcdn.net/100/514/823/themes/995743/assets/policies_icon_4.png?1759032268598" alt="policies_icon_4.png" 
						 width="40"
						 height="40"
						 class="img-fluid">
				</div>
				<div class="policies-info">
					<h3 class="policies-title">Hotline: 0387239676</h3>
					<div class="policies-desc">Dịch vụ hỗ trợ bạn 24/7</div>
				</div>
			</div>
		</div>
	</div>
</div>
</section>
<section class="section awe-section-16">	
	<section class="section_ss_collection section">
	<div class="container border-0">
	<h2 class="heading-bar__title"><span>our</span>CATEGORY</h2>
	<div class="ss_body">
		<div class="row mx-0 hrz-scroll text-center flex-nowrap js-slider justify-content-around btn-slide--new">
		
			
																																				<div class="ss_item style2">
					<a href="/den-tha-tran">
						<div class="ss_img">
							<picture>
								<source media="(max-width: 991px)" srcset="//bizweb.dktcdn.net/thumb/medium/100/514/823/themes/995743/assets/season_coll_1_img.png?1759032268598">
								<img loading="lazy" 
									 class="img-fluid m-auto object-contain mh-100 w-auto" 
									 src="assets\images\index-c1.webp"
									 width="200"
									 height="200"
									 alt="season_coll_1_img.png"/>
							</picture>
						</div>
						<div class="ss_info">
							<div class="ss_name">ĐÈN THẢ TRẦN</div>
							<span class="ss_number">30 sản phẩm</span>
							<div class="ss_seemore">

<svg class="icon" >
	<use xlink:href="#icon-arrow" />
</svg></div>
						</div> 
					</a>
				</div>
																																								<div class="ss_item style2">
					<a href="/den-tuong">
						<div class="ss_img">
							<picture>
								<source media="(max-width: 991px)" srcset="//bizweb.dktcdn.net/thumb/medium/100/514/823/themes/995743/assets/season_coll_2_img.png?1759032268598">
								<img loading="lazy" 
									 class="img-fluid m-auto object-contain mh-100 w-auto" 
									 src="assets\images\index-c2.webp"
									 width="200"
									 height="200"
									 alt="season_coll_2_img.png"/>
							</picture>
						</div>
						<div class="ss_info">
							<div class="ss_name">ĐÈN TƯỜNG</div>
							<span class="ss_number">15 sản phẩm</span>
							<div class="ss_seemore">

<svg class="icon" >
	<use xlink:href="#icon-arrow" />
</svg></div>
						</div> 
					</a>
				</div>
																																								<div class="ss_item style2">
					<a href="/den-ban">
						<div class="ss_img">
							<picture>
								<source media="(max-width: 991px)" srcset="//bizweb.dktcdn.net/thumb/medium/100/514/823/themes/995743/assets/season_coll_3_img.png?1759032268598">
								<img loading="lazy" 
									 class="img-fluid m-auto object-contain mh-100 w-auto" 
									 src="assets\images\index-c3.webp"
									 width="200"
									 height="200"
									 alt="season_coll_3_img.png"/>
							</picture>
						</div>
						<div class="ss_info">
							<div class="ss_name">ĐÈN BÀN</div>
							<span class="ss_number">25 sản phẩm</span>
							<div class="ss_seemore">

<svg class="icon" >
	<use xlink:href="#icon-arrow" />
</svg></div>
						</div> 
					</a>
				</div>
																																								<div class="ss_item style2">
					<a href="/den-san">
						<div class="ss_img">
							<picture>
								<source media="(max-width: 991px)" srcset="//bizweb.dktcdn.net/thumb/medium/100/514/823/themes/995743/assets/season_coll_4_img.png?1759032268598">
								<img loading="lazy" 
									 class="img-fluid m-auto object-contain mh-100 w-auto" 
									 src="assets\images\index-c4.webp"
									 width="200"
									 height="200"
									 alt="season_coll_4_img.png"/>
							</picture>
						</div>
						<div class="ss_info">
							<div class="ss_name">ĐÈN SÀN</div>
							<span class="ss_number">10 sản phẩm</span>
							<div class="ss_seemore">

<svg class="icon" >
	<use xlink:href="#icon-arrow" />
</svg></div>
						</div> 
					</a>
				</div>
																																								<div class="ss_item style2">
					<a href="/den-chum">
						<div class="ss_img">
							<picture>
								<source media="(max-width: 991px)" srcset="//bizweb.dktcdn.net/thumb/medium/100/514/823/themes/995743/assets/season_coll_5_img.png?1759032268598">
								<img loading="lazy" 
									 class="img-fluid m-auto object-contain mh-100 w-auto" 
									 src="assets\images\index-c5.webp"
									 width="200"
									 height="200"
									 alt="season_coll_5_img.png"/>
							</picture>
						</div>
						<div class="ss_info">
							<div class="ss_name">ĐÈN CHÙM</div>
							<span class="ss_number">5 sản phẩm</span>
							<div class="ss_seemore">

<svg class="icon" >
	<use xlink:href="#icon-arrow" />
</svg></div>
						</div> 
					</a>
				</div>
																																								<div class="ss_item style2">
					<a href="/ke-trang-tri">
						<div class="ss_img">
							<picture>
								<source media="(max-width: 991px)" srcset="//bizweb.dktcdn.net/thumb/medium/100/514/823/themes/995743/assets/season_coll_6_img.png?1759032268598">
								<img loading="lazy" 
									 class="img-fluid m-auto object-contain mh-100 w-auto" 
									 src="assets\images\index-c6.webp"
									 width="200"
									 height="200"
									 alt="season_coll_6_img.png"/>
							</picture>
						</div>
						<div class="ss_info">
							<div class="ss_name">KỆ TRANG TRÍ</div>
							<span class="ss_number">4 sản phẩm</span>
							<div class="ss_seemore">

<svg class="icon" >
	<use xlink:href="#icon-arrow" />
</svg></div>
						</div> 
					</a>
				</div>
																																																																			
		</div>
	</div>
	</div>
</section>
</section>
<section class="about">
    <div class="about-body">

        <div class="about-img">
            <source media="(max-width: 991px)" srcset="assets/images/index-about.webp">
            <img src="assets/images/index-about.webp" 
            class="img-fluid mx-auto"
            width="795"
			height="475"
            alt="">
        </div>
        <div class="about-content">
            <h2>VỀ CHÚNG TÔI</h2>
            <p>
               Tại Winsum Home Decor, chúng tôi tin rằng đèn không chỉ là công cụ chiếu sáng, mà còn là yếu tố quan trọng tạo nên không gian sống đầy cảm hứng.
Chúng tôi chuyên cung cấp các mẫu đèn trang trí cao cấp – tinh tế trong thiết kế, chất lượng trong từng chi tiết – mang đến sự ấm áp, hiện đại và gu thẩm mỹ riêng cho từng ngôi nhà.
Winsum tự hào là người bạn đồng hành cùng bạn trong hành trình ánh sáng.
            </p>
            <a href="#">Xem chi tiết</a>
        </div>
    </div>
</section>
<section class="section_blog">
    <div class="container">
        <!-- TITLE -->
        <div class="blog-header">
            <h2><span>our</span> BLOG</h2>
            <a href="#">Xem tất cả</a>
        </div>
        <!-- LIST -->
        <div class="blog-list">
            <!-- ITEM -->
            <div class="blog-item">
                <img src="assets/images/index-blog1.webp" alt="">
                <div class="blog-content">
                    <h3>
                        Đèn Treo Trần AXIS Thông Minh:
                        Giải Pháp Tối Ưu...
                    </h3>
                    <a href="#">XEM NGAY</a>
                </div>
            </div>
            <!-- ITEM -->
            <div class="blog-item">
                <img src="assets/images/index-blog2.webp" alt="">
                <div class="blog-content">
                    <h3>
                        Điểm Nhấn Hoài Cổ:
                        Khám Phá Vẻ Đẹp...
                    </h3>
                    <a href="#">XEM NGAY</a>
                </div>
            </div>
            <!-- ITEM -->
            <div class="blog-item">
                <img src="assets/images/index-blog3.webp" alt="">
                <div class="blog-content">
                    <h3>
                        PH5 PENDANT LAMP:
                        Tuyệt Tác Ánh Sáng...
                    </h3>
                    <a href="#">XEM NGAY</a>
                </div>
            </div>
            <!-- ITEM -->
            <div class="blog-item">
                <img src="assets/images/index-blog4.webp" alt="">
                <div class="blog-content">
                    <h3>
                        Vẻ Đẹp Từ Thiên Nhiên –
                        Tại Sao Đèn...
                    </h3>
                    <a href="#">XEM NGAY</a>
                </div>
            </div>
        </div>
    </div>
</section>
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
