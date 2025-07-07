jQuery(document).ready(function($) {
  $('#project-type-filter').on('change', function() {
    let selectedType = $(this).val();

    $.ajax({
      url: project_ajax_object.ajax_url,
      type: 'POST',
      data: {
        action: 'filter_projects',
        project_type: selectedType
      },
      beforeSend: function() {
        $('#projects-list').html('<p>Loading...</p>');
      },
      success: function(response) {
        $('#projects-list').html(response);
      }
    });
  });
});
