
		function openLink(url) {
			window.location.href = url;
		}


		// open a link in the same tag!
		function open_link(mylink)
		{
			// open link in the same window
			window.open(mylink, "_self")
		}


        // Go back to the previous page if it exists!
		function goBack()
		{
		   window.history.back();
		}


		function addOption2SelectBox(element_ID, element_value, element_text)
		{

			var selectBox = $("#" + element_ID);

			selectBox.append($('<option>',
			{
				value: element_value,
				text: element_text
			}));

		}

		function emptySelectBox(element_ID)
		{
			$('#' + element_ID).empty();
		}





		// sets the focus of an element. Can be used on input fields etc
		function set_Focus_On_Element_By_ID(element_ID)
		{
			//document.getElementById("id_product_barcode").focus();
			$('#' + element_ID).focus();
		}


		// as per the title...
		function empty_Element_By_ID(element_ID)
		{
			$('#' + element_ID).empty();
		}


		// Append some HTML code to an element
		function append_HTML_to_Element_By_ID(element_ID, html_code)
		{
			$('#' + element_ID).append(html_code);
		}


		// Set HTML on an element
		function set_HTML_to_Element_By_ID(element_ID, html_code)
		{
			$('#' + element_ID).html(html_code);
		}




		// return a value of a given element via ID
		function get_Element_Object_By_ID(element_ID)
		{
			return document.getElementById(element_ID);
		}


		// return a value of a given element via ID
		function get_Element_Value_By_ID(element_ID)
		{
			return document.getElementById(element_ID).value;
		}


		// return text from a given SelectBox by ID
		// This needs Jquery to work !
		function get_SelectBox_Text_By_ID(element_ID)
		{
			return $('#' + element_ID + ' option:selected').text();
		}


		// set the value of an element via ID
		function set_Element_Value_By_ID(element_ID, setValue)
		{
			document.getElementById(element_ID).value	=	setValue;
		}


		// set color of an element via ID	- useful for button color setting...
		function set_Element_Style_Color_By_ID(element_ID, setColor)
		{
			document.getElementById(element_ID).style.color	=	setColor;
		}


		// get color of an element via ID	- useful for button color setting...
		function get_Element_Style_Color_By_ID(element_ID)
		{
			return document.getElementById(element_ID).style.color;
		}


		// disable an element via ID
		function disable_Element(element_ID)
		{
			document.getElementById(element_ID).disabled = true;			
		}


		// enable an element via ID
		function enable_Element(element_ID)
		{
			document.getElementById(element_ID).disabled = false;			
		}


		// disable an element via ID
		function disable_Element_alt(element_ID)
		{
			$( '#' + element_ID).prop( 'disabled', true );
		}


		// enable an element via ID
		function enable_Element_alt(element_ID)
		{
			$( '#' + element_ID).prop( 'disabled', false );
		}


		//$( '#id_ssh_btn_toggle' ).prop( 'disabled', true );
		//$( '#id_ssh_btn_toggle' ).prop( 'disabled', false );


		/* Remove all items from a given object (table?) via ID */
		function remove_All_Rows_From_Table(element_ID)
		{
			$("#" + element_ID).empty();
		}


		/*	Generic insert row function		*/
		function displayRow(table_name, column_sizes, data)
		{

			var clen	=	column_sizes.length;		// how many columns in the table to create !

			var start	=	'<tr>';
			var core	=	'';
			var end		=	'</tr>';

			// create columns and add them to the table as a row !
			for (var i = 0; i < clen; i++)
			{
				core	=	core	+	'<td width="' + column_sizes[i]  + '%" >' + data[i] + '</td>';
			}

			$('#' + table_name + ' tbody').append(start + core + end);

		}


		function hide_table_column(table_name, col_num)
		{
			$("#" + table_name + " td:nth-child(" + col_num + ")").hide();			
		}

		
		function hide_object(element_ID)
		{
			$("#" + element_ID).hide();
		}


		function show_object(element_ID)
		{
			$("#" + element_ID).show();
		}


		function highlightRowByDataId(selectedId, tableId)
		{
			// Remove highlight from all rows
			//$(`#${tableId} tr`).removeClass('highlighted');
			
			// Find the row with the matching data-id and add the highlight class
			$(`#${tableId} tr[data-id="${selectedId}"]`).addClass('highlighted');
		}


		function updateRow(selectedId, tableId, newValues)
		{
			// Find the row with the matching data-id
			let row = $(`#${tableId} tr[data-id="${selectedId}"]`);
			
			// Loop through each cell and update its value
			row.find('td').each(function(index)
			{
				$(this).text(newValues[index]);
			});
		}
		
		
		function disableRowByDataId(selectedId, tableId)
		{
			// Find the row with the matching data-id and add the highlight class
			//$(`#${tableId} tr[data-id="${selectedId}"]`).removeClass('highlighted');
			$(`#${tableId} tr[data-id="${selectedId}"]`).addClass('red_class');
		}


		function enableRowByDataId(selectedId, tableId)
		{
			// Find the row with the matching data-id and add the highlight class
			$(`#${tableId} tr[data-id="${selectedId}"]`).removeClass('red_class');
			$(`#${tableId} tr[data-id="${selectedId}"]`).addClass('highlighted');
		}
		
		