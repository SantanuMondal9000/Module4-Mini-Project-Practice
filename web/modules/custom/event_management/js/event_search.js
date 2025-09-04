(function ($, Drupal, once) {
  Drupal.behaviors.eventSearch = {
    attach: function (context, settings) {
      // Make sure this runs only once per element
      once('eventSearch', '#event-search', context).forEach(function (element) {
        $(element).on('keyup', function () {
          console.log("Key pressed:", $(this).val());

          // Test AJAX call
          $.ajax({
            url: '/event-search-ajax',
            method: 'GET',
            data: { search: $(this).val() },
            success: function (response) {
              console.log("AJAX Response:", response);
              $('#event-list').html(response.html);
            },
            error: function () {
              console.error("Error loading events");
            }
          });
        });
      });
    }
  };
})(jQuery, Drupal, once);
