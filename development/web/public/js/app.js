// @codekit-prepend quiet '../../node_modules/jquery/dist/jquery.min.js'
// @codekit-prepend quiet '../../node_modules/lazysizes/lazysizes.min.js'
// @codekit-prepend quiet '../../node_modules/rellax/rellax.min.js'
// @codekit-prepend quiet '../vendor/swiper/dist/js/swiper.min.js'
// @codekit-prepend quiet '../vendor/colcade/colcade.js'
// @codekit-prepend quiet '../vendor/jquery-modal/jquery.modal.js'
// @codekit-prepend quiet '../vendor/numscroller/numscroller-1.0.js'
// @codekit-prepend quiet '../vendor/wow/dist/wow.js'

(function ($) { // Begin jQuery
	$(function () { // DOM ready
		// navigation
		$(".nav__button").on("click", function () {
			if ($("body").hasClass("nav-open")) {
				$(".nav__button").removeClass("is-active");
				$("body").removeClass("nav-open");
			} else {
				$(".nav__button").addClass("is-active");
				$("body").addClass("nav-open");
			}
		});

		// swiper: init multiple instances
		var swiperInstances1 = {};
		$(".swiper--1").each(function (index, element) {
			var $this = $(this);
			$this.addClass("swiper1-instance-" + index);
			$this.find(".swiper-button-prev").addClass("swiper1-btn-prev-" + index);
			$this.find(".swiper-button-next").addClass("swiper1-btn-next-" + index);
			$this.find(".swiper-pagination").addClass("swiper1-pagination-" + index);
			swiperInstances1[index] = new Swiper(".swiper1-instance-" + index, {
				loop: false,
				grabCursor: true,
				autoHeight: true,
				spaceBetween: 32,
				slidesPerView: 1,
				pagination: {
					el: ".swiper1-pagination-" + index,
					type: "bullets",
					clickable: true
				},
				navigation: {
					prevEl: ".swiper1-btn-prev-" + index,
					nextEl: ".swiper1-btn-next-" + index
				}
			});
		});

		var swiperInstances2 = {};
		$(".swiper--2").each(function (index, element) {
			var $this = $(this);
			$this.addClass("swiper2-instance-" + index);
			$this.find(".swiper-button-prev").addClass("swiper2-btn-prev-" + index);
			$this.find(".swiper-button-next").addClass("swiper2-btn-next-" + index);
			$this.find(".swiper-pagination").addClass("swiper2-pagination-" + index);
			swiperInstances2[index] = new Swiper(".swiper2-instance-" + index, {
				loop: false,
				grabCursor: true,
				autoHeight: true,
				spaceBetween: 32,
				slidesPerView: 2,
				pagination: {
					el: ".swiper2-pagination-" + index,
					type: "bullets",
					clickable: true
				},
				navigation: {
					prevEl: ".swiper2-btn-prev-" + index,
					nextEl: ".swiper2-btn-next-" + index
				},
				breakpoints: {
					640: {
						slidesPerView: 1
					}
				}
			});
		});

		// colcade
		$(".gridx").colcade({
			columns: ".gridx-col",
			items: ".gridx-item"
		});

		// dropdown
		$(".dropdown__item").on("change", function () {
			$(".dropdown__item")
				.not(this)
				.prop("checked", false);
		});

		// js-tv
		// open tv
		$('.js-tv').click(function () {
			event.preventDefault();
			var myTv = $(this).attr('data-tv');
			var myAnchor = $(this).attr('data-tv');

			$('.tv--active').removeClass('tv--active');
			$('section[data-tv = ' + myTv + ']').addClass('tv--active');

			$('html,body').animate({
				scrollTop: $('#' + myAnchor).offset().top,
				easing: "linear"
			}, 300);
		});

		// close tv
		$('.js-tv-close').click(function () {
			$('.tv--active').removeClass('tv--active');
			$('html,body').animate({
				scrollTop: $('#start-tv').offset().top,
				easing: "linear"
			}, 300);
		});

		// js-modal
		$(".js-modal").click(function (event) {
			event.preventDefault();
			$(this).modal({
				fadeDuration: 250
			});
		});

		// fire rellax
		var rellax = new Rellax('.rellax', {
			speed: -1,
			center: true,
			wrapper: null,
			round: true,
			vertical: true,
			horizontal: false
		});

		// fire wow
		$(function () {
			wow = new WOW({
				offset: 160,
				mobile: true
			});
			wow.init();
		});

	}); // end DOM ready
})(jQuery); // end jQuery
