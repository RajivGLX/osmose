import $ from 'jquery';

$(document).ready( function(){
    App.init();
});

var App = {
    init : function(){

        this.Tools.init();

        const $body = $('body');
        if($body.attr('id') === 'home') HomePage.init();
        if($body.attr('id') === 'app_booking') BookingCenterPage.init();
        if($body.attr('id') === 'account_booking') BookingAccountPage.init();
        if($body.attr('id') === 'account_information') InformationAccountPage.init();
        if($body.attr('id') === 'app_user_manual') UserManual.init();
        if(window.location.pathname.includes('/connexion')) SecurityPage.init();

    },
  Tools: {
    init: function () {
      this.showPassword();
      this.initSelectPicker();
    },

    showPassword: function () {
      $(document)
        .off('click.showPassword', '.input-group-text')
        .on('click.showPassword', '.input-group-text', function () {
          const $btn = $(this);
          const $input = $btn.closest('.input-group').find('input[type="password"], input[type="text"]').first();
          const nextType = $input.attr('type') === 'password' ? 'text' : 'password';
          $input.attr('type', nextType);
          $btn.find('.fa-eye, .fa-eye-slash').toggleClass('d-none');
        });
    },

    initSelectPicker: function () {
      if ($.fn.selectpicker) {
        $('.selectpicker').selectpicker();
      } else {
        console.warn('bootstrap-select non chargé');
      }
    }
  }
}

var HomePage = {
    init : function(){

    }
}

var BookingAccountPage = {
    init : function(){
        this.modalCanceled();
    },
    modalCanceled : function () {
        let openModal = $("[data-open-modal]");
        let modal = $("[data-modal]");
        let overlay = $("[data-overlay]");
        let closeModal = $("[data-close-modal]");

        openModal.click(() => {
            modal.show();
            overlay.show();
        });

        overlay.click(() => {
            modal.hide();
            overlay.hide();
        });

        closeModal.click(() => {
            modal.hide();
            overlay.hide();
        });
    }
}

var BookingCenterPage = {
    init : function(){
        this.oneSwitchForOneDay();
        this.popupConfirmBooking();
        this.ajaxCalendar();
        this.ajaxPopup();
    },
    oneSwitchForOneDay : function () {
        $(document).on('change', '.blockDay input[type="checkbox"]', function() {
            var $this = $(this);
            var isChecked = $this.is(":checked");
            var day = $this.attr('data-title');
            var timeslot = $this.attr('class');
            var value = $this.val();
            var name = $this.attr('name');

            // Gérer le formulaire
            var $form = $('form');
            var $existingInput = $form.find('input[type="checkbox"][name="' + name + '"]');

            // Gérer la liste des séances sélectionnées
            var $selectionList = $('.widget-categories ul');
            var $existingItem = $selectionList.find('li:contains("' + day + '")');

            // Désactiver les autres checkboxes dans le même blockDay
            var blockDay = $this.closest('.blockDay');

            if (isChecked) {
                // Si l'élément n'existe pas déjà dans la liste, on crée un nouveau li avec les informations du jour et du créneau horaire et on l'ajoute à la liste.
                if (!$existingItem.length) {
                    var $newItem = $('<li><span>' + day + '</span> <span class="float-right">' + timeslot + '</span></li>');
                    $selectionList.append($newItem);
                } else {
                    // Mettre à jour le li existant si le jour et le timeslot sont différents
                    if ($existingItem.find('.float-right').text() !== timeslot) {
                        $existingItem.find('.float-right').text(timeslot);
                    }
                }

                // Si l'input n'existe pas déjà dans le formulaire, on crée un nouvel input avec les mêmes valeurs et on l'ajoute au formulaire.
                if (!$existingInput.length) {
                    var $newInput = $('<input type="checkbox" name="' + name + '" value="' + value + '" class="' + timeslot + '" data-title="' + day + '" checked hidden>');
                    $form.append($newInput);
                } else {
                    // Si l'input existe déjà, on met à jour sa valeur et sa classe
                    $existingInput.val(value);
                    $existingInput.attr('class', timeslot);
                }

                // Désactiver les autres checkboxes dans le même blockDay
                blockDay.find('input[type="checkbox"]').not($this).prop('checked', false);
            } else {
                // Si l'élément existe, on le supprime de la liste.
                $this.prop('checked', false);
                $existingItem.remove();

                // Si l'input existe, on le supprime du formulaire.
                $existingInput.remove();
            }
        });
    },
    popupConfirmBooking : function () {
        $(".openModal").on('click', function() {
            var errorSelect = $(".errorSelect");
            var form = $(".bookingForm");
            var reasonField = $("#booking_reason");
            var reason = reasonField.val().trim();
            var comment = $("#booking_comment").val();

            errorSelect.hide(); // Réinitialisez le message d'erreur

            if (reason === "") {
                // Définissez un message de validation personnalisé
                reasonField[0].setCustomValidity("Veuillez remplir le motif de la réservation.");

                // Déclenchez l'événement 'invalid' sur le champ pour afficher le message d'erreur
                reasonField[0].reportValidity();

                return; // Arrête l'exécution de la fonction si le champ "reason" est vide
            } else {
                // Si le champ est rempli, assurez-vous que le message de validation personnalisé est réinitialisé
                reasonField[0].setCustomValidity("");
            }

            // Récupérez les dates sélectionnées
            var selectedDates = [];
            form.find('input[type="checkbox"]:checked').each(function() {
                var date = $(this).attr('data-title');
                var slot = $(this).attr('class');
                selectedDates.push('<br>' + date + ' (' + slot + ')');
            });

            // Vérifiez si au moins une date a été sélectionnée
            if (selectedDates.length === 0) {
                errorSelect.show(); 
                return;
            }

            // Mettez à jour le contenu de la popup avec ces valeurs
            $(".selectedDate").html(selectedDates.join(' '));
            $(".reason").html(reason);
            $(".comment").html(comment);


            // Affichez la popup
            $(".popup-by-notation").addClass('model-open');
        });
        $(".close-btn, .bg-overlay").click(function(){
            $(".custom-model-main").removeClass('model-open');
        });
    },
    ajaxCalendar : function () {
        $(document).on('click', '.navigationCalendar a', function(e) {
            e.preventDefault();

            var url = $(this).attr('href');

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    // Remplacez le HTML actuel du calendrier par le nouveau HTML
                    $('.boxCalendar').html($(response).find('.boxCalendar').html());

                    // Cochez les dates sélectionnées dans le calendrier
                    var $form = $('form');
                    $form.find('input[type="checkbox"]').each(function() {
                        var value = $(this).val();
                        var name = $(this).attr('name');
                        $('.boxCalendar input[type="checkbox"][value="' + value + '"][name="' + name + '"]').prop('checked', true);
                    });
                },
                error: function() {
                    alert('Une erreur est survenue lors du chargement du calendrier.');
                }
            });
        });
    },
    ajaxPopup: function() {
        $('#patientInformationForm').on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);
            var url = $form.attr('action');
            var formData = $form.serialize();

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        console.log(response.message);
                        $(".popup-confirm").addClass('model-open');
                        $(".popup-info").removeClass('model-open');
                        $(".popup-confirm").addClass('popup-by-notation');
                        $(".popup-info").removeClass('popup-by-notation');
                    } else {
                        var errors = response.errors;
                        $.each(errors, function(field, errorMessage) {
                            var $field = $form.find('[name="patient_information_min[' + field + ']"]');
                            console.log($field);
                            console.log(errors);
                            $field.addClass('is-invalid');
                            $field.next('.invalid-feedback').remove(); // Supprimer les anciens messages d'erreur
                            $field.after('<div class="invalid-feedback">' + errorMessage + '</div>');
                        });
                    }
                },
                error: function() {
                    alert('Une erreur est survenue lors de la soumission du formulaire.');
                }
            });
        });
    }

}


var InformationAccountPage = {
    init : function(){
        window.onload = function() {
            // Sélectionnez tous les champs de formulaire avec une erreur
            var errorFields = document.querySelectorAll('.form-control.is-invalid');

            // Si il y a des erreurs, faites défiler la page jusqu'à la première erreur
            if (errorFields.length > 0) {
                var rect = errorFields[0].getBoundingClientRect();
                var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                window.scrollTo({ top: rect.top + scrollTop - 200, behavior: 'smooth' });
            }
        };
    }
}

var UserManual = {
    init : function(){
        // Cache tous les paragraphes sauf le premier initialement
        $('.accordionManual div > p').hide();
        $('.accordionManual div:first-child > p').show();

        // Ajoute un écouteur d'événement de clic sur chaque titre h3 dans l'accordéon
        $('.accordionManual div > h3').click(function() {
            var $this = $(this);
            var $nextParagraph = $this.next('p');

            // Vérifie si le paragraphe suivant est déjà ouvert
            if ($nextParagraph.is(':visible')) {
                // Si le paragraphe est déjà ouvert, ne faites rien pour éviter de le fermer
                return false;
            }

            // Ferme tous les paragraphes ouverts, sauf celui qui est frère du titre cliqué (toggle)
            $('.accordionManual div > p').not($nextParagraph).slideUp();
            $nextParagraph.slideDown();

            // Empêche le comportement par défaut de l'ancre (si utilisé)
            return false;
        });
    }
}


var SecurityPage = {
    init: function() {
      this.initAccordion();
    },

    initAccordion: function() {
        // Initialiser l'état
        $('.accordion .collapse').removeClass('show');
        $('.accordion .card-header').addClass('collapsed').attr('aria-expanded', 'false');

        $('.accordion .card-header').off('click.accordion').on('click.accordion', function(e) {
            e.preventDefault();

            var $header = $(this);
            var targetId = $header.attr('href');
            var $target = $(targetId);
            var $accordion = $header.closest('.accordion');
            var isOpen = $target.hasClass('show');

            if (!isOpen) {
                // Fermer tous les autres
                $accordion.find('.collapse.show').removeClass('show');
                $accordion.find('.card-header').addClass('collapsed').attr('aria-expanded', 'false');

                // Ouvrir celui-ci
                $target.addClass('show');
                $header.removeClass('collapsed').attr('aria-expanded', 'true');
            } else {
                // Fermer celui-ci
                $target.removeClass('show');
                $header.addClass('collapsed').attr('aria-expanded', 'false');
            }
      });
    }
};
