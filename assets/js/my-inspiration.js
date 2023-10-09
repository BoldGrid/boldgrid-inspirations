
/**
 * File: assets/js/my-inspiration.js
 * 
 * This File is loaded on the My Inspirations Page, and
 * is responsible for dynamic updating of the onboarding progress
 * 
 * @since   2.8.0
 * @package BoldGrid Inspirations
 * @author  jamesros161
 */
jQuery( document ).ready( function( $ ) {
	/**
	 * Format Progress
	 * 
	 * @since 2.8.0
	 * 
	 * @param {float} completedDecimal The percentage of tasks completed
	 * 
	 * @return {string} The formatted progress
	 */
	var formatProgress = ( completedDecimal ) => {
		return Math.round( completedDecimal * 100 ) + '%';
	}

	/**
	 * Update Topbar Progress
	 * 
	 * @since 2.8.0
	 * 
	 * @param {float} completedDecimal The percentage of tasks completed
	 */
	var updateTopbarProgress = ( completedDecimal ) => {
		var formattedProgress = formatProgress( completedDecimal ),
			$progressSpan     = $( '#toplevel_page_boldgrid-inspirations .bginsp-progress' );

		$progressSpan.html( formattedProgress );

		if ( '100%' === formattedProgress ) {
			$progressSpan.addClass( 'complete' );
		} else {
			$progressSpan.removeClass( 'complete' );
		}

	};

	/**
	 * Update Progress Bar
	 * 
	 * @since 2.8.0
	 *
	 * @param {float} completedDecimal The percentage of tasks completed
	 */
	var updateProgressBar = ( completedDecimal ) => {
		var $progressBar    = $( '.onboarding-progress-bar' ),
			$progressText   = $progressBar.find( 'span.percent-complete' ),
			percentComplete = formatProgress( completedDecimal );

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

	/**
	 * Expand or Collapse Task
	 * 
	 * @since 2.8.0
	 * 
	 * @param {jQuery} $task       The task element
	 * @param {boolean} isComplete Whether the task is complete or not
	 */
	var expandOrCollapse = ( $task, isComplete ) => {
		if ( isComplete && ! $task.hasClass( 'collapsed' ) ) {
			$task.addClass( 'collapsed' );
		}

		if ( ! isComplete && $task.hasClass( 'collapsed' ) ) {
			$task.removeClass( 'collapsed' );
		}
	};

	/**
	 * Update Task Status
	 *  
	 * @since 2.8.0
	 *
	 * @param {string}  taskId     ID of the task
	 * @param {boolean} isComplete Whether the task is complete or not
	 * @param {string}  nonce      Nonce for the AJAX request
	 * @param {jQuery}  $target    Event Target used to redirect the user
	 */
	var updateTaskStatus = ( taskId, isComplete, nonce, $target ) => {
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
		} ).done( () => {
			if ( $target.attr( 'href' ) ) {
				window.location.href = $target.attr( 'href' );
			}
		} );
	};

	/**
	 * Handle Click Checkbox
	 * 
	 * @since 2.8.0
	 *
	 * @param {MouseEvent} e Mouse Click Event
	 */
	var handleClickCheckbox = ( e ) => {
		var $target    = $( e.currentTarget ),
			$task      = $target.closest( '.boldgrid-onboarding-task' ),
			nonce      = $( '.onboarding-nonce' ).data( 'nonce' ),
			totalTasks = $( '.onboarding-cards .boldgrid-onboarding-task' ).length,
			completeTasks,
			completedDecimal,
			isComplete;

			/*
			 * If the user is clicking a button for a completed task,
			 * we don't want to proceed to mark it incomplete. That should only
			 * be done if they manually uncheck a completed item.
			 */
			if ( $target.hasClass( 'button' ) && $task.hasClass( 'complete' ) ) {
				return;
			}

			if ( $target.hasClass( 'skip-all-tasks' ) ) {
				$( '.onboarding-cards .boldgrid-onboarding-task' ).addClass( 'complete' );
				updateTaskStatus( 'skip_all_tasks', true, nonce, $target );
			} else {
				e.preventDefault();
				$task.toggleClass( 'complete' );
				isComplete = $task.hasClass( 'complete' );
				updateTaskStatus( $task.attr( 'id' ), isComplete, nonce, $target );
				expandOrCollapse( $task, isComplete );
			}

			completeTasks    = $( '.boldgrid-onboarding-task.complete' ).length;
			completedDecimal = completeTasks / totalTasks;

			updateTopbarProgress( completedDecimal );
			updateProgressBar( completedDecimal );
				
	};

	/**
	 * Handle Click Arrow
	 *
	 * @since 2.8.0
	 *
	 * @param {MouseEvent} e Mouse Click Event
	 */
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
