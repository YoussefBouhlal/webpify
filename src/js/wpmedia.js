import '../scss/wpmedia.scss';

const WpMedia = () => {
	const { ajaxUrl, nonce } = WEBPIFY_WPMEDIA; // eslint-disable-line no-undef

	/*
	 * Handle click on the page
	 *
	 * @param {Event} e Click event.
	 */
	const handleClick = ( e ) => {
		const singleOptimizeBtn = e.target.closest(
			'button.webpify-single-optimization-btn'
		);
		const undoSingleOptimizeBtn = e.target.closest(
			'button.webpify-undo-single-optimization-btn'
		);

		if ( singleOptimizeBtn ) {
			startOptimization( e, singleOptimizeBtn );
		} else if ( undoSingleOptimizeBtn ) {
			undoOptimization( e, undoSingleOptimizeBtn );
		}
	};

	/*
	 * Start optimization button clicked
	 *
	 * @param {Event} e Click event.
	 * @param {HTMLElement} btn Button element.
	 */
	const startOptimization = ( e, btn ) => {
		e.preventDefault();

		showSpinner( btn );

		const form = new FormData();
		form.append( 'action', 'webpify_single_optimization_start' );
		form.append( '_wpnonce', nonce );
		form.append( 'attachment_id', e.target.dataset.attachmentId );

		fetch( ajaxUrl, {
			method: 'POST',
			body: form,
		} )
			.then( ( r ) => r.json() )
			.then( ( res ) => {
				hideSpinner( btn );

				if ( res.success ) {
					const td = btn.closest( 'td' );
					td.innerHTML = '';
					td.innerHTML = res.data;
				} else {
					const msg = btn.nextElementSibling;
					msg.innerHTML = res.data;
					msg.classList.add( 'show' );
				}
			} )
			.catch( () => {
				hideSpinner( btn );
			} );
	};

	/*
	 * Undo optimization button clicked
	 *
	 * @param {Event} e Click event.
	 * @param {HTMLElement} btn Button element.
	 */
	const undoOptimization = ( e, btn ) => {
		e.preventDefault();

		showSpinner( btn );

		const form = new FormData();
		form.append( 'action', 'webpify_single_optimization_undo' );
		form.append( '_wpnonce', nonce );
		form.append( 'attachment_id', e.target.dataset.attachmentId );

		fetch( ajaxUrl, {
			method: 'POST',
			body: form,
		} )
			.then( ( r ) => r.json() )
			.then( ( res ) => {
				hideSpinner( btn );

				if ( res.success ) {
					const td = btn.closest( 'td' );
					td.innerHTML = '';
					td.innerHTML = res.data;
				} else {
					const msg = btn.nextElementSibling;
					msg.innerHTML = res.data;
					msg.classList.add( 'show' );
				}
			} )
			.catch( () => {
				hideSpinner( btn );
			} );
	};

	/*
	 * Show button spinner
	 *
	 * @param {HTMLElement} btn Button element.
	 */
	const showSpinner = ( btn ) => {
		btn.classList.add( 'show-spinner' );
	};

	/*
	 * Hide button spinner
	 *
	 * @param {HTMLElement} btn Button element.
	 */
	const hideSpinner = ( btn ) => {
		btn.classList.remove( 'show-spinner' );
	};

	/**
	 * Events listenners
	 */
	document.addEventListener( 'click', ( e ) => {
		handleClick( e );
	} );
};
document.addEventListener( 'DOMContentLoaded', WpMedia );
