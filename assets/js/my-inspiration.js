
jQuery(document).ready(function($) {
	var updateTopbarProgress = ( completedDecimal ) => {
		var formattedProgress = Math.round( completedDecimal * 100 ) + '%',
			$progressAnchor    = $( '#wp-admin-bar-inspirations-onboarding-progress a' );

		$progressAnchor.attr( 'title', formattedProgress )
			.find( 'span' ).html( formattedProgress );

		if ( '100%' === formattedProgress ) {
			$( '#wp-admin-bar-inspirations-onboarding-progress' ).addClass( 'bginsp-progress-complete' );
		} else {
			$( '#wp-admin-bar-inspirations-onboarding-progress' ).removeClass( 'bginsp-progress-complete' );
		}

	};

	var updateProgressBar = ( completedDecimal ) => {
		var $progressBar    = $( '.onboarding-progress-bar' ),
			$progressText   = $progressBar.find( 'span.percent-complete' ),
			percentComplete = Math.round( completedDecimal * 100 ) + '%';

		$progressBar.attr( 'style', '--percent-complete: ' + percentComplete );

		$progressText.html( percentComplete );

		if ( '100%' === percentComplete ) {
			$( '.instructions' ).addClass( 'hidden' );
			$( '.completion' ).removeClass( 'hidden' );
		} else {
			$( '.instructions' ).removeClass( 'hidden' );
			$( '.completion' ).addClass( 'hidden' );
		}
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
			nonce      = $( '.onboarding-nonce' ).data( 'nonce' ),
			totalTasks = $( '.onboarding-cards .boldgrid-onboarding-task' ).length,
			completeTasks,
			completedDecimal,
			isComplete;

			console.log( {
				nonce: $( '.onboarding-nonce' ).data( 'nonce' ),
			} );

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
	$( '.boldgrid-onboarding-task .task-title' ).on( 'click', handleClickArrow );
	$( '.boldgrid-onboarding-task .task-buttons .button.complete-on-click' ).on( 'click', handleClickCheckbox );

} );
