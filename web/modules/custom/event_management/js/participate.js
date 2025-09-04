(function ($, Drupal, once) {
  Drupal.behaviors.participateButton = {
    attach: function (context, settings) {

      // Attach once to all participate buttons
      once('participateButton', '#participate-btn', context).forEach(function (element) {
        var $btn = $(element);
        var nid = $btn.data('nid');

        // $.get('/participate/check/' + nid, function (res) {
        //   console.log(res.status);
        //   if (res.status === 'participated') {
        //     $btn.text('Participated').prop('disabled', true).data('participated', 1);
        //   } else {
        //     $btn.text('Participate').prop('disabled', false).data('participated', 0);
        //   }
        // });

        // If already participated, mark and disable
        if ($btn.attr('data-participated') === '1' || $btn.data('participated') === 1) { $btn.text('Participated').prop('disabled', true).attr('disabled', 'disabled'); return; }

        // Click handler
        $btn.on('click', function (e) {
          e.preventDefault();
          var $thisBtn = $(this);
          console.log("buggg");
          $thisBtn.prop('disabled', true).text('Submitting...');
          var $removeBtn = $thisBtn
            .closest('.participate-action-area')
            .find('.participate-remove-btn'); 
          $removeBtn.prop('disabled', false);
          // Get CSRF token
          $.get('/session/token')
            .done(function (token) {
              $.ajax({
                url: '/participate/ajax',
                type: 'POST',
                dataType: 'json',
                headers: { 'X-CSRF-Token': token },
                data: { nid: nid },
                success: function (res) {
                  if (res.status === 'success' || res.status === 'already') {
                    // $thisBtn.text('Participated').prop('disabled', true);
                    // $thisBtn.data('participated', 1);
                    // console.log(res.message+"dasdasd");
                    $thisBtn.text('Participated').prop('disabled', true).attr('disabled', 'disabled').data('participated', 1).attr('data-participated', '1');
                  } else {
                    alert(res.message || 'Error');
                    $thisBtn.prop('disabled', false).text('Participate');
                  }
                },
                error: function () {
                  alert('Request failed.');
                  $thisBtn.prop('disabled', false).text('Participate');
                }
              });
            })
            .fail(function () {
              alert('Could not get CSRF token.');
              $thisBtn.prop('disabled', false).text('Participate');
            });
        });
      });

    }
  };
})(jQuery, Drupal, once);

//For the Paricipate remove button.
(function ($, Drupal, once) {
  Drupal.behaviors.participateRemoveButton = {
    attach: function (context, settings) {

      // Attach once to all participate buttons
      once('participateRemoveButton', '#participate-remove-btn', context).forEach(function (element) {
        var $btn = $(element);
        var nid = $btn.data('nid');
        // If already participated, mark and disable
        if ($btn.attr('data-participated') === '1' || $btn.data('participated') === 1) 
        { 
          $btn.prop('disabled', false).removeAttr('disabled');
        }
        //Click handler
        $btn.on('click', function (e) {
          e.preventDefault();
          console.log("remove");
          var $thisBtn = $(this);
          var $participateBtn = $thisBtn
            .closest('.participate-action-area')
            .find('.participate-btn'); 
          // $participateBtn.prop('disabled', false).text('Participate');
        
          // Get CSRF token
          $.get('/session/token')
            .done(function (token) {
              $.ajax({
                url: '/participate-remove/ajax',
                type: 'POST',
                dataType: 'json',
                headers: { 'X-CSRF-Token': token },
                data: { nid: nid },
                success: function (res) {
                  if (res.status === 'success' || res.status === 'already') {
                    // $thisBtn.text('Participated').prop('disabled', true);
                    // $thisBtn.data('participated', 1);
                    // console.log(res.message+"dasdasd");
                    // $thisBtn.text('Participated').prop('disabled', true).attr('disabled', 'disabled').data('participated', 1).attr('data-participated', '1');
                    console.log("sucess");
                    $thisBtn.prop('disabled', true).attr('disabled', 'disabled').data('participated', 0).attr('data-participated', '0');
                    $participateBtn.text('Participate').prop('disabled', false).removeAttr('disabled').data('participated', 0).attr('data-participated', '0');
                  } else {
                    alert(res.message || 'Error');
                    // $thisBtn.prop('disabled', false).text('Participate');
                  }
                },
                error: function () {
                  alert('Request failed.');
                  // $thisBtn.prop('disabled', false).text('Participate');
                }
              });
            })
            .fail(function () {
              alert('Could not get CSRF token.');
              // $thisBtn.prop('disabled', false).text('Participate');
            });
        });
      });

    }
  };
})(jQuery, Drupal, once);
