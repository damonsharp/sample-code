var SomeNamespace = SomeNamespace || {};

(function($, $f){

	// Controls the Woo Flexslider custom implementation
	// Videos loaded via Vimeo and are swapped based on clicking thumbnails below
	// the player. Clicking the video to play shows "Now Playing" (localized in Spanish also)
	SomeNamespace.Slider = {

		init: function() {

			this.thumbs = $('#slider').find('.thumb-item img');
			this.windowWidth = window.innerWidth;
			this.video = $('#video')[0];
			this.videoSrc = $(this.video).attr('src').split('?');
			this.videoSrcUrl = this.videoSrc[0];
			this.videoPlayParams = this.videoSrc[1];
			this.player = $f(this.video); // Utilizes Vimeo's Froogaloop js library
			this.videoCaption = $('#video-caption');
			this.videoCaptionTitle = this.videoCaption.find('.caption-title');
			this.videoCaptionDesc = this.videoCaption.find('.caption-description');
			this.videoPlayContainer = $('#video-play-container');
			this.videoPoster = $('#video-play-overlay');
			this.videoPlayBtn = $('#video-play-btn');
			this.numThumbs = 5;
			this.slider = $('#slider').flexslider({
				animation: 'slide',
				animationLoop: false,
				itemWidth: 95,
				itemMargin: 0,
				minItems: 2,
				maxItems: this.numThumbs,
				slideshow: false,
				controlNav: false,
				directionNav: true,
				prevText: '',
				nextText: ''
			});

			this.makeVideoReponsive();
			this.setPlayBtnPosition();
			this.activateThumbs();
			this.setupThumbNav();
			this.playVideo();

		},

		// Set the thumbnails to their active state when clicked and on first load.
		activateThumbs: function() {

			var self = this,
				videoIndex = $(self.video).data('index');

			self.thumbs.on('click touchstart', function(e) {

				e.preventDefault();
			
				var thumb = $(this),
					videoFileSrc = thumb.data('videofilesrc'),
					videoPoster = thumb.data('videoposter'),
					videoCaptionTitle = thumb.data('videocaptiontitle'),
					videoCaptionDesc = thumb.data('videocaptiondesc'),
					videoOnClick = thumb.data('onclick'),
					thumbIndex = thumb.data('index');

				self.videoPlayContainer.removeClass('hidden');
				self.videoPlayBtn.attr('onClick', videoOnClick);
				self.videoPoster.attr('src', videoPoster);
				$(self.video).attr('src', videoFileSrc);
				self.videoCaptionTitle.html(videoCaptionTitle);
				self.videoCaptionDesc.html(videoCaptionDesc);
				self.thumbs.parent().removeClass('pager-active').find('span').addClass('hidden');
				self.videoCaption.removeClass('hidden');
				thumb.parent().addClass('pager-active');

				// Change the video element's attributes...
				$(self.video).load().attr('poster', videoPoster).attr('data-index', thumbIndex);

			}).first().trigger('click');

		},

		setupThumbNav: function() {

			var self = this;

			$('.slider-prev').on('click touchstart', function(e){

				e.preventDefault();
				self.slider.flexslider('prev');

			});

			$('.slider-next').on('click touchstart', function(e){

				e.preventDefault();
				self.slider.flexslider('next');

			});

		},

		playVideo: function() {

			var self = this;

			self.videoPlayBtn.bind('click touchstart', { video: $(self.video), caption: $(self.videoCaption) }, function(e){

				self.player.api('play');
				self.videoPlayContainer.addClass('hidden');
				self.videoCaption.addClass('hidden');
				self.thumbs.parent().removeClass('pager-active');
				self.thumbs.eq(e.data.video.attr('data-index')).parent().addClass('pager-active').find('.hidden').removeClass('hidden');
			});

		},

		// Keep play button overlay centered vertically and horizonatally
		setPlayBtnPosition: function() {

			var self = this,
				videos = $('#video-wrapper');

			$(window).on('resize orientationchange', function(){

				var btnWidth = self.videoPlayBtn.width(),
					btnHeight = self.videoPlayBtn.height(),
					parentWidth = videos.width(),
					parentHeight = videos.height(),
					btnTop = (parentHeight - btnHeight)/2;
					btnLeft = (parentWidth - btnWidth)/2;

				self.videoPlayBtn.css({

					top: btnTop,
					left: btnLeft

				});

			}).trigger('resize');

		},

		makeVideoReponsive: function() {

			$('#video-wrapper').fitVids({

				customSelector: "iframe"

			});

		}

	};

	SomeNamespace.Slider.init();


})(jQuery, Froogaloop);