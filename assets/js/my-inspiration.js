
jQuery(document).ready(function($) {
	var updateTopbarProgress = ( completedDecimal ) => {
		var formattedProgress = Math.round( completedDecimal * 100 ) + '%',
			$progressSpan     = $( '#toplevel_page_boldgrid-inspirations .bginsp-progress' );

		$progressSpan.html( formattedProgress );

		if ( '100%' === formattedProgress ) {
			$progressSpan.addClass( 'complete' );
		} else {
			$progressSpan.removeClass( 'complete' );
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

	var updateTaskStatus = ( taskId, isComplete, nonce ) => {
		$.ajax( {
			type: 'post',
			url: ajaxurl,
			dataType: 'json',
			data: {
				action: 'boldgrid_inspirations_update_task',
				nonce: nonce,
				task_id: taskId,
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

			if ( $target.hasClass( 'button' ) && $task.hasClass( 'complete' ) ) {
				return;
			}

			if ( $target.hasClass( 'skip-all-tasks' ) ) {
				$( '.onboarding-cards .boldgrid-onboarding-task' ).addClass( 'complete' );
				updateTaskStatus( 'skip_all_tasks', true, nonce );
			} else {
				$task.toggleClass( 'complete' );
				isComplete = $task.hasClass( 'complete' );
				updateTaskStatus( $task.attr( 'id' ), isComplete, nonce );
				expandOrCollapse( $task, isComplete );
			}

			completeTasks    = $( '.boldgrid-onboarding-task.complete' ).length;
			completedDecimal = completeTasks / totalTasks;

			updateTopbarProgress( completedDecimal );
			updateProgressBar( completedDecimal );
				
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
	$( '.my-inspirations-header .button.skip-all-tasks' ).on( 'click', handleClickCheckbox );

} );
