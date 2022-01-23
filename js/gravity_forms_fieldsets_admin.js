jQuery(document).ready(function($) {

	fieldSettings["FieldsetBegin"] = ".label_setting, .css_class_setting, .conditional_logic_field_setting, .admin_label_setting, .label_placement_setting";
	fieldSettings["FieldsetEnd"] = ".label_setting, .css_class_setting, .conditional_logic_field_setting, .admin_label_setting, .label_placement_setting";

	function fieldsetExist() {
		var fieldsetsCount = jQuery('#gform_fields .gform_fieldset').length;
		return fieldsetsCount;
	}

	jQuery(document).bind('gform_field_added', function(event, form, field) {

		if (field['type'] == 'FieldsetBegin' || field['type'] == 'FieldsetEnd') {

			var fieldsetClosed = true;
			var index = 1;

			jQuery.each(form.fields, function(index, formField) {

				if (typeof formField.type != 'undefined') {
					if (formField.type == 'FieldsetBegin') {
						if (fieldsetClosed) {
							fieldsetClosed = false;
						} else {
							StartAddField('FieldsetEnd', index);
							fieldsetClosed = true;
							return;
						}
					} else if (formField.type == 'FieldsetEnd') {
						if ( fieldsetClosed ) {
							StartAddField('FieldsetBegin', index);
							return;
						} else {
							fieldsetClosed = true;
						}
					}
				}
				index++;

			});

			if (!fieldsetClosed) {
				StartAddField('FieldsetEnd');
			}

		}

	});

	jQuery(document).bind('gform_field_deleted', function(event, form, field){

		console.log('field deleted:', field);

		var fieldsetClosed = true;

		jQuery.each( form.fields, function(index, formField) {
			if (typeof formField.type != 'undefined') {
				if (formField.type == 'FieldsetBegin') {
					fieldsetClosed = false;
				} else if (formField.type == 'FieldsetEnd') {
					if (fieldsetClosed) {
						deleteFieldset(formField.id);
						return;
					}
					fieldsetClosed = true;
				}
			}

		});

	});

	function deleteFieldset(fieldId) {

		jQuery('#gform_fields div#field_' + fieldId).addClass('gform_pending_delete');

		// start modified copy of DeleteField method (from Gravity Forms: form_editor.js)
		event.stopPropagation();
		if (HasConditionalLogicDependency(fieldId) && !confirm(gf_vars.conditionalLogicDependency)) {
			return;
		}
		if (!form.deletedFields) {
			form.deletedFields = [];
		}
		form.deletedFields.push(fieldId);
		for (var i = 0; i < form.fields.length; i++) {
			if (form.fields[i].id == fieldId) {
				form.fields.splice(i, 1);
				jQuery('#field_' + fieldId).fadeOut('slow', function () {
					jQuery('#field_' + fieldId).remove();
					if (form.fields.length === 0) {
						jQuery('#no-fields').show();
					}
					gform.doAction('gform_after_field_removed', form, fieldId);
				});
				HideSettings('field_settings');
				break;
			}
		}
		jQuery('.sidebar').tabs('option', 'active', 0)
		TogglePageBreakSettings();
		// end modified copy of DeleteField method

	}

});