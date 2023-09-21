
jQuery(document).ready(function($) {
	var updateTopbarProgress = ( completedDecimal ) => {
		$( '#wp-admin-bar-inspirations-onboarding-progress span.bginsp-progress' ).html(
			Math.round( completedDecimal * 100 ) + '%'
		);
	};

	var updateProgressBar = ( completedDecimal ) => {
		var $progressBar    = $( '.onboarding-progress-bar' ),
			$progressText   = $progressBar.find( 'span.percent-complete' ),
			percentComplete = Math.round( completedDecimal * 100 ) + '%';

		$progressBar.attr( 'style', '--percent-complete: ' + percentComplete );

		$progressText.html( percentComplete );
	};

	var expandOrCollapse = ( $task, isComplete ) => {
		if ( isComplete && ! $task.hasClass( 'collapsed' ) ) {
			$task.addClass( 'collapsed' );
		}

		if ( ! isComplete && $task.hasClass( 'collapsed' ) ) {
			$task.removeClass( 'collapsed' );
		}
	};

	var updateTaskStatus = ( $task, isComplete, nonce ) => {
		$.ajax( {
			type: 'post',
			url: ajaxurl,
			dataType: 'json',
			data: {
				action: 'boldgrid_inspirations_update_task',
				nonce: nonce,
				task_id: $task.attr( 'id' ),
				task_complete: isComplete
			}
		} );
	};

	var handleClickCheckbox = ( e ) => {
		var $target    = $( e.currentTarget ),
			$task      = $target.closest( '.boldgrid-onboarding-task' ),
			nonce      = $target.closest( '.onboarding-cards').find( '.onboarding-nonce' ).data( 'nonce' ),
			totalTasks = $( '.boldgrid-onboarding-task' ).length,
			completeTasks,
			completedDecimal,
			isComplete;

			if ( $target.hasClass( 'button' ) && $task.hasClass( 'complete' ) ) {
				return;
			}

			$task.toggleClass( 'complete' );

			isComplete = $task.hasClass( 'complete' );

			completeTasks    = $( '.boldgrid-onboarding-task.complete' ).length;
			completedDecimal = completeTasks / totalTasks;

			updateTopbarProgress( completedDecimal );
			updateProgressBar( completedDecimal );
			expandOrCollapse( $task, isComplete );

			updateTaskStatus( $task, isComplete, nonce );	
	};

	var handleClickArrow = ( e ) => {
		var $target = $( e.currentTarget ),
			$task   = $target.closest( '.boldgrid-onboarding-task' );

		$task.toggleClass( 'collapsed' );
	};

	$( '.boldgrid-onboarding-task-checkbox span' ).on( 'click', handleClickCheckbox );
	$( '.boldgrid-onboarding-task .collapse-expand' ).on( 'click', handleClickArrow );
	$( '.boldgrid-onboarding-task .task-buttons .button.complete-on-click' ).on( 'click', handleClickCheckbox );
} );
