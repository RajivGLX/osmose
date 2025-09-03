import $ from 'jquery';
import './vendors/hs.megamenu.js';
import 'bootstrap';

window.jQuery = window.$ = $;

/*================
 Template Name: Hostlar Hosting Provider with WHMCS Template
 Description: All type of web hosting provider or company with WHMCS template.
 Version: 1.0
 Author: https://themeforest.net/user/themetags
=======================*/
// TABLE OF CONTENTS
// 1. preloader
// 2. mega menu js
// 3. fixed navbar
// 4. scroll bottom to top
// 5. custom vps hosting plan js
// 6. monthly and yearly pricing switch
// 7. tooltip
// 8. magnify popup video
// 9. hero slider one
// 10. hero slider two
// 11. client-testimonial carousel
// 12. client logo item carousel
// 13. team member carousel
// 14. video background
// 15. wow js
// 16. countdown or coming soon
// 17. sticky sidebar
// 18. chat api js
// 19. image gallery js
// 20. contact form js
jQuery(function ($) {
  'use strict';

  // // 1. preloader - Version améliorée sans Turbo
  // $(window).on('load', function () {
  //   console.log('Window loaded - hiding preloader');
  //   $('#preloader').delay(200).fadeOut('slow');
  // });
  //
  // // Gestion du preloader pour les formulaires avec navigation classique
  // $(document).on('submit', 'form', function(e) {
  //   console.log('Form submitted - showing preloader');
  //
  //   // Affiche le preloader immédiatement
  //   $('#preloader').show();
  //
  //   // Cache le preloader en cas d'erreur de validation côté client
  //   // ou si le formulaire ne se soumet pas correctement
  //   setTimeout(function() {
  //     if ($('#preloader').is(':visible')) {
  //       console.log('Timeout - checking if still on same page');
  //       // Si on est toujours sur la même page après 5 secondes,
  //       // c'est probablement une erreur de validation
  //       $('#preloader').fadeOut('slow');
  //     }
  //   }, 5000);
  // });
  //
  // // Gestion du preloader pour les liens de navigation
  // $(document).on('click', 'a[href]:not([target="_blank"]):not([href^="#"]):not([href^="javascript:"]):not([href^="mailto:"]):not([href^="tel:"])', function(e) {
  //   var href = $(this).attr('href');
  //
  //   // Ignore les liens externes ou spéciaux
  //   if (href && !href.startsWith('/') && !href.includes(window.location.hostname)) {
  //     return;
  //   }
  //
  //   console.log('Navigation link clicked - showing preloader');
  //   $('#preloader').show();
  //
  //   // Cache le preloader après un délai de sécurité
  //   setTimeout(function() {
  //     $('#preloader').fadeOut('slow');
  //   }, 3000);
  // });
  //
  // // Cache le preloader si la page se charge rapidement
  // $(document).ready(function() {
  //   setTimeout(function() {
  //     if ($('#preloader').is(':visible')) {
  //       console.log('Document ready timeout - hiding preloader');
  //       $('#preloader').fadeOut('slow');
  //     }
  //   }, 1000);
  // });

  // 2. mega menu js
  $('.js-mega-menu').HSMegaMenu({
    event: 'hover',
    pageContainer: $('.container'),
    breakpoint: 767.98,
    hideTimeOut: 0
  });

  // 3. fixed navbar
  $(window).on('scroll', function () {
    // checks if window is scrolled more than 500px, adds/removes solid class
    if ($(this).scrollTop() > 100) {
      $('.main-header-menu-wrap').addClass('affix');
    } else {
      $('.main-header-menu-wrap').removeClass('affix');
    }
  });

  // 4. scroll bottom to top
  $(window).on('scroll', function () {
    if ($(window).scrollTop() > $(window).height()) {
      $('.scroll-to-target').addClass('open');
    } else {
      $('.scroll-to-target').removeClass('open');
    }

    if ($('.scroll-to-target').length) {
      $(".scroll-to-target").on('click', function () {
        var target = $(this).attr('data-target');
        var new_time = new Date();

        if (!this.old_time || new_time - this.old_time > 1000) {
          // animate
          $('html, body').animate({
            scrollTop: $(target).offset().top
          }, 500);
          this.old_time = new_time;
        }
      });
    }
  });
  
  var cPlan = $('#c-plan');

  if (cPlan.length) {
    cPlan.slider({
      tooltip: 'always'
    });
    cPlan.on("slide", function (e) {
      $.each(vpsPriceInfo, function (index, vpsObj) {
        if (vpsObj.vpsPlan == e.value) {
          setVpsValue(vpsObj);
        }
      });
    });
    initSlider();
  }

  function initSlider() {
    cPlan.value = cPlan.data("slider-value");
    var defaultVpsCore = parseInt(cPlan.value);
    $.each(vpsPriceInfo, function (index, vpsObj) {
      if (vpsObj.vpsPlan == defaultVpsCore) {
        $('.slider .tooltip', '#custom-plan').append('<div class="tooltip-up"></div>');
        $('.slider .tooltip-inner', '#custom-plan').attr("data-unit", cPlan.data("unit"));
        $('.slider .tooltip-up', '#custom-plan').attr("data-currency", cPlan.data("currency"));
        setVpsValue(vpsObj);
      }
    });
  }

  function setVpsValue(vpsObj) {
    $('.slider .tooltip-up', '#custom-plan').text(vpsObj.vpsPrice);
    $('.vpsPrice', '#custom-plan').text(cPlan.data("currency") + vpsObj.vpsPrice);
    $('.vpsCore span', '#custom-plan').text(vpsObj.vpsCore);
    $('.vpsMemory span', '#custom-plan').text(vpsObj.vpsMemory);
    $('.vpsStorage span', '#custom-plan').text(vpsObj.vpsStorage);
    $('.vpsBandwidth span', '#custom-plan').text(vpsObj.vpsBandwidth);
    $('.vpsWHmcsUrl', '#custom-plan').attr("href", vpsObj.vpsWHmcsUrl);
  }

  // 6. monthly, yearly, biannual and triennial pricing switch
  if ($(".billingCycle").length > 0) {
    var billingPlanInputs = $("input[name='billingPlan']");
    billingPlanInputs.change(function () {
      var billingPlan = $(this).val();
      $.each(['.monthly-price', '.yearly-price', '.biannual-price', '.triennial-price'], function (index, tag) {
        $(tag).css('display', 'none');
      });
      $('.' + billingPlan + '-price').css('display', 'block');
    });
  }

  // 7. tooltip
  $('.custom-map-location li span').tooltip('show');

  // 17. sticky sidebar

  $(function () {
    // document ready
    if ($('#sticky').length) {
      // make sure "#sticky" element exists
      var el = $('#sticky');
      var stickyTop = $('#sticky').offset().top; // returns number

      var stickyHeight = $('#sticky').height();
      $(window).scroll(function () {
        // scroll event
        var limit = $('#section-footer').offset().top - stickyHeight - 20;
        var windowTop = $(window).scrollTop(); // returns number

        if (stickyTop < windowTop) {
          el.css({
            position: 'fixed',
            top: 20,
            width: 350
          });
        } else {
          el.css('position', 'static');
        }

        if (limit < windowTop) {
          var diff = limit - windowTop;
          el.css({
            top: diff
          });
        }
      });
    }
  }); // 18. chat api js

  var Tawk_API = Tawk_API || {},
      Tawk_LoadStart = new Date();



  $('.domain-filter-title').on('click', function () {
    $('.domain-filter-list').fadeToggle("slow");
  });

 // 20. contact form

  if ($("#contactForm").length) {
    setCsrf();
    $("#contactForm").validator().on("submit", function (event) {
      if (event.isDefaultPrevented()) {
        // handle the invalid form...
        submitMSG(false);
      } else {
        // everything looks good!
        event.preventDefault();
        submitForm();
      }
    });
  }

  function submitForm() {
    // Initiate Variables With Form Content
    var name = $("#name").val();
    var email = $("#email").val();
    var message = $("#message").val();
    var csrfToken = $("#csrfToken").val();

    if (csrfToken) {
      if (name && email && message) {
        $.ajax({
          type: "POST",
          url: "libs/contact-form-process.php",
          data: "name=" + name + "&email=" + email + "&message=" + message + "&csrfToken=" + csrfToken,
          success: function success(text) {
            if (text == "success") {
              formSuccess();
            } else {
              submitMSG(false, text);
            }
          }
        });
      } else {
        submitMSG(false, "Please enter the right information.");
      }
    } else {
      submitMSG(false, "Invalid Token");
    }
  }

  function formSuccess() {
    $("#contactForm")[0].reset();
    submitMSG(true);
  }

  function submitMSG(valid, msg) {
    if (valid) {
      $(".message-box").removeClass('d-none').addClass('d-block ');
      $(".message-box div").removeClass('alert-danger').addClass('alert-success').text('Form submitted successfully');
    } else {
      $(".message-box").removeClass('d-none').addClass('d-block ');
      $(".message-box div").removeClass('alert-success').addClass('alert-danger').text('Found error in the form. Please check again.');
    }
  }

  function setCsrf() {
    $.ajax({
      url: 'libs/csrf.php',
      type: "GET",
      dataType: "json",
      success: function success(data) {
        if (data) {
          document.getElementById("csrfToken").value = data.csrfToken;
        }
      },
      error: function error(_error) {
        console.log("Error " + _error);
      }
    });
  }
});
// JQuery end