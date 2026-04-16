/**
 * Zeka Appointment Booking - Calendar Management
 */

( function() {
	'use strict';

	const Calendar = {
		currentAppointmentId: null,
		currentDate: null,

		/**
		 * Initialize calendar functionality.
		 */
		init() {
			this.cacheElements();
			this.bindEvents();
			this.loadAllAppointments();
		},

		/**
		 * Cache DOM elements.
		 */
		cacheElements() {
			this.$modal = document.getElementById( 'zab-appointment-modal' );
			this.$form = document.getElementById( 'zab-appointment-form' );
			this.$addBtn = document.getElementById( 'zab-add-appointment-btn' );
			this.$deleteBtn = document.getElementById( 'zab-delete-btn' );
			this.$appointmentIdInput = document.getElementById( 'zab-appointment-id' );
			this.$appointmentDateInput = document.getElementById( 'zab-appointment-date' );
			this.$appointmentTimeInput = document.getElementById( 'zab-appointment-time' );
			this.$serviceSelect = document.getElementById( 'zab-service-select' );
			this.$statusSelect = document.getElementById( 'zab-appointment-status' );
			this.$modalTitle = document.getElementById( 'zab-modal-title' );
		},

		/**
		 * Bind event handlers.
		 */
		bindEvents() {
			// Add appointment button.
			this.$addBtn.addEventListener( 'click', () => this.showAddModal() );

			// Calendar cells.
			document.querySelectorAll( '.zab-calendar-cell' ).forEach( cell => {
				cell.addEventListener( 'click', ( e ) => {
					if ( e.target.classList.contains( 'zab-appointment-tag' ) ) {
						this.editAppointment( e.target );
					} else {
						const date = cell.dataset.date;
						this.showAddModal( date );
					}
				} );
			} );

			// Modal close buttons.
			document.querySelectorAll( '.zab-modal-close, .zab-modal-close-btn' ).forEach( btn => {
				btn.addEventListener( 'click', () => this.closeModal() );
			} );

			// Modal close on background click.
			this.$modal.addEventListener( 'click', ( e ) => {
				if ( e.target === this.$modal ) {
					this.closeModal();
				}
			} );

			// Form submission.
			this.$form.addEventListener( 'submit', ( e ) => {
				e.preventDefault();
				this.saveAppointment();
			} );

			// Delete button.
			this.$deleteBtn.addEventListener( 'click', () => this.deleteAppointment() );
		},

		/**
		 * Load all appointments for the calendar.
		 */
		loadAllAppointments() {
			const cells = document.querySelectorAll( '.zab-calendar-cell' );

			cells.forEach( cell => {
				const date = cell.dataset.date;
				this.loadAppointmentsForDate( date );
			} );
		},

		/**
		 * Load appointments for a specific date.
		 *
		 * @param {string} date Date in Y-m-d format.
		 */
		loadAppointmentsForDate( date ) {
			const data = new FormData();
			data.append( 'action', 'zab_get_appointments' );
			data.append( 'nonce', zabCalendarData.nonce );
			data.append( 'date', date );

			fetch( zabCalendarData.ajaxUrl, {
				method: 'POST',
				body: data,
			} )
				.then( res => res.json() )
				.then( res => {
					if ( res.success ) {
						this.renderAppointments( date, res.data );
					}
				} )
				.catch( err => console.error( 'Error loading appointments:', err ) );
		},

		/**
		 * Render appointments for a date.
		 *
		 * @param {string} date Date in Y-m-d format.
		 * @param {Array} appointments Array of appointment objects.
		 */
		renderAppointments( date, appointments ) {
			const container = document.getElementById( `appointments-${ date }` );
			if ( ! container ) return;

			container.innerHTML = '';

			appointments.forEach( appt => {
				const tag = document.createElement( 'div' );
				tag.className = `zab-appointment-tag ${ appt.status }`;
				tag.textContent = `${ appt.time } - ${ appt.service_name }`;
				tag.dataset.appointmentId = appt.id;
				tag.addEventListener( 'click', () => this.editAppointmentId( appt.id, date ) );
				container.appendChild( tag );
			} );
		},

		/**
		 * Show add appointment modal.
		 *
		 * @param {string} date Optional date in Y-m-d format.
		 */
		showAddModal( date = null ) {
			this.currentAppointmentId = null;
			this.currentDate = date || zabCalendarData.currentDate;

			this.$modalTitle.textContent = 'Add Appointment';
			this.$appointmentIdInput.value = '';
			this.$appointmentDateInput.value = this.currentDate;
			this.$appointmentTimeInput.value = '09:00';
			this.$serviceSelect.value = '';
			this.$statusSelect.value = 'confirmed';
			this.$deleteBtn.style.display = 'none';

			this.$modal.classList.add( 'active' );
		},

		/**
		 * Edit appointment by ID.
		 *
		 * @param {number} appointmentId Appointment ID.
		 * @param {string} date Date in Y-m-d format.
		 */
		editAppointmentId( appointmentId, date ) {
			const tag = document.querySelector( `[data-appointment-id="${ appointmentId }"]` );
			if ( tag ) {
				this.editAppointment( tag );
			}
		},

		/**
		 * Edit appointment from tag element.
		 *
		 * @param {HTMLElement} tagElement The appointment tag element.
		 */
		editAppointment( tagElement ) {
			const appointmentId = tagElement.dataset.appointmentId;
			this.currentAppointmentId = appointmentId;

			// For now, just show basic edit modal with reload capability.
			// In production, would fetch full appointment details via AJAX.
			this.$modalTitle.textContent = 'Edit Appointment';
			this.$appointmentIdInput.value = appointmentId;
			this.$deleteBtn.style.display = 'inline-block';

			this.$modal.classList.add( 'active' );
		},

		/**
		 * Save appointment (create or update).
		 */
		saveAppointment() {
			const appointmentId = this.$appointmentIdInput.value;
			const date = this.$appointmentDateInput.value;
			const time = this.$appointmentTimeInput.value;
			const serviceId = this.$serviceSelect.value;
			const status = this.$statusSelect.value;
			const customerName = document.getElementById( 'zab-customer-name' ).value;
			const customerEmail = document.getElementById( 'zab-customer-email' ).value;

			if ( ! date || ! time || ! serviceId ) {
				this.showAlert( 'Please fill in all required fields.', 'error' );
				return;
			}

			const action = appointmentId ? 'zab_update_appointment' : 'zab_create_appointment';
			const data = new FormData();
			data.append( 'action', action );
			data.append( 'nonce', zabCalendarData.nonce );
			data.append( 'appointment_id', appointmentId );
			data.append( 'appointment_date', date );
			data.append( 'appointment_time', time );
			data.append( 'service_id', serviceId );
			data.append( 'status', status );
			data.append( 'customer_name', customerName );
			data.append( 'customer_email', customerEmail );

			this.$form.classList.add( 'zab-loading' );

			fetch( zabCalendarData.ajaxUrl, {
				method: 'POST',
				body: data,
			} )
				.then( res => res.json() )
				.then( res => {
					this.$form.classList.remove( 'zab-loading' );

					if ( res.success ) {
						this.showAlert( appointmentId ? 'Appointment updated!' : 'Appointment created!', 'success' );
						this.closeModal();
						this.loadAppointmentsForDate( date );
					} else {
						this.showAlert( res.data.message || 'Error saving appointment.', 'error' );
					}
				} )
				.catch( err => {
					this.$form.classList.remove( 'zab-loading' );
					console.error( 'Error saving appointment:', err );
					this.showAlert( 'An error occurred.', 'error' );
				} );
		},

		/**
		 * Delete appointment.
		 */
		deleteAppointment() {
			if ( ! this.currentAppointmentId ) return;
			if ( ! confirm( 'Are you sure you want to delete this appointment?' ) ) return;

			const data = new FormData();
			data.append( 'action', 'zab_delete_appointment' );
			data.append( 'nonce', zabCalendarData.nonce );
			data.append( 'appointment_id', this.currentAppointmentId );

			this.$form.classList.add( 'zab-loading' );

			fetch( zabCalendarData.ajaxUrl, {
				method: 'POST',
				body: data,
			} )
				.then( res => res.json() )
				.then( res => {
					this.$form.classList.remove( 'zab-loading' );

					if ( res.success ) {
						this.showAlert( 'Appointment deleted!', 'success' );
						this.closeModal();
						const date = this.$appointmentDateInput.value;
						this.loadAppointmentsForDate( date );
					} else {
						this.showAlert( res.data.message || 'Error deleting appointment.', 'error' );
					}
				} )
				.catch( err => {
					this.$form.classList.remove( 'zab-loading' );
					console.error( 'Error deleting appointment:', err );
					this.showAlert( 'An error occurred.', 'error' );
				} );
		},

		/**
		 * Close modal.
		 */
		closeModal() {
			this.$modal.classList.remove( 'active' );
			this.$form.reset();
		},

		/**
		 * Show alert message.
		 *
		 * @param {string} message Message to display.
		 * @param {string} type Alert type (success/error).
		 */
		showAlert( message, type = 'info' ) {
			const alert = document.createElement( 'div' );
			alert.className = `zab-alert zab-alert-${ type } show`;
			alert.textContent = message;

			const modal = this.$modal.querySelector( '.zab-modal-content' );
			const form = this.$form;
			if ( form.previousElementSibling?.classList.contains( 'zab-alert' ) ) {
				form.previousElementSibling.remove();
			}
			form.insertAdjacentElement( 'beforebegin', alert );

			setTimeout( () => alert.remove(), 5000 );
		},

		/**
		 * Get today's date in Y-m-d format.
		 *
		 * @return {string}
		 */
	};

	// Initialize on document ready.
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', () => Calendar.init() );
	} else {
		Calendar.init();
	}
} )();
