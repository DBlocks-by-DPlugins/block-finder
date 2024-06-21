jQuery(document).ready(function($) {
    function toggleClearButton() {
        if ($('#bf-search-input').val() === '') {
            $('#bf-clear-button').hide();
        } else {
            $('#bf-clear-button').show();
        }
    }

    function toggleNoResultsBanner() {
        if ($('.bf-block-group:visible').length === 0) {
            $('#bf-no-results').show();
        } else {
            $('#bf-no-results').hide();
        }
    }

    $('#bf-search-input').on('keyup', function(event) {
        var searchQuery = $(this).val().toLowerCase();
        
        if (event.key === 'Escape') {
            $(this).val('');
            searchQuery = '';
        }

        toggleClearButton();

        $('.bf-block-group').each(function() {
            var blockTitle = $(this).data('block-title').toLowerCase();
            if (blockTitle.indexOf(searchQuery) !== -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        toggleNoResultsBanner();
    });

    $('#bf-clear-button').on('click', function() {
        $('#bf-search-input').val('');
        toggleClearButton();
        $('.bf-block-group').show();
        toggleNoResultsBanner();
    });

    // Initial call to set the correct state of the clear button and no results banner
    toggleClearButton();
    toggleNoResultsBanner();
});