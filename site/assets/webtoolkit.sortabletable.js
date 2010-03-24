/**
*
*  Sortable HTML table
*  http://www.webtoolkit.info/
*
**/

function SortableTable (tableEl, asc, desc) {

	this.tbody = tableEl.getElementsByTagName('tbody');
	this.thead = tableEl.getElementsByTagName('thead');
	this.tfoot = tableEl.getElementsByTagName('tfoot');

	this.HasClassName = function (objElement, strClass) {
		// if there is a class
		if ( objElement.className ) {
			// the classes are just a space separated list, so first get the list
			var arrList = objElement.className.split(' ');
			// get uppercase class for comparison purposes
			var strClassUpper = strClass.toUpperCase();
			// find all instances and remove them
			for ( var i = 0; i < arrList.length; i++ ) {
				if ( arrList[i].toUpperCase() == strClassUpper ) {
					return true;
				}
			}
		}
		// if we got here then the class name is not there
		return false;
	}
	
	this.AddClassName = function (objElement, strClass, blnMayAlreadyExist) {
		if ( objElement.className ) {
			// the classes are just a space separated list, so first get the list
			var arrList = objElement.className.split(' ');
			// if the new class name may already exist in list
			if ( blnMayAlreadyExist ) {
				var strClassUpper = strClass.toUpperCase();
				// find all instances and remove them
				for ( var i = 0; i < arrList.length; i++ ) {
					if ( arrList[i].toUpperCase() == strClassUpper ) {
						// remove array item
						arrList.splice(i, 1);
						i--;
					}
				}
			}
			// add the new class to end of list
			arrList[arrList.length] = strClass;
		 	// add the new class to beginning of list
			//arrList.splice(0, 0, strClass);
		 	// assign modified class name attribute
			objElement.className = arrList.join(' ');
		}
		// if there was no class
		else {
			// assign modified class name attribute      
			objElement.className = strClass;
		}
	}
	
	this.RemoveClassName = function (objElement, strClass) {
	 	// if there is a class
		if ( objElement.className ) {
	 		// the classes are just a space separated list, so first get the list
			var arrList = objElement.className.split(' ');
	 		// get uppercase class for comparison purposes
			var strClassUpper = strClass.toUpperCase();
	 		// find all instances and remove them
	 		for ( var i = 0; i < arrList.length; i++ ) {
	 			// if class found
				if ( arrList[i].toUpperCase() == strClassUpper ) {
	 				// remove array item
	 				arrList.splice(i, 1);
	 				// decrement loop counter as we have adjusted the array's contents
	 				i--;
	 			}
	 		}
	 		// assign modified class name attribute
			objElement.className = arrList.join(' ');
	 	}
		// if there was no class
		// there is nothing to remove
	}

	this.getInnerText = function (el) {
		if (typeof(el.textContent) != 'undefined') return el.textContent;
		if (typeof(el.innerText) != 'undefined') return el.innerText;
		if (typeof(el.innerHTML) == 'string') return el.innerHTML.replace(/<[^<>]+>/g,'');
	}

	this.getParent = function (el, pTagName) {
		if (el == null) return null;
		else if (el.nodeType == 1 && el.tagName.toLowerCase() == pTagName.toLowerCase())
			return el;
		else
			return this.getParent(el.parentNode, pTagName);
	}

	this.sort = function (cell) {
	    var column = cell.cellIndex;
	    var itm = this.getInnerText(this.tbody[0].rows[1].cells[column]);
		var sortfn = this.sortCaseInsensitive;

		if (itm.match(/\d\d[-]+\d\d[-]+\d\d\d\d/)) sortfn = this.sortDate; // date format mm-dd-yyyy
		if (itm.replace(/^\s+|\s+$/g,"").match(/^-?[\d\.]+$/)) { sortfn = this.sortNumeric; }

		this.sortColumnIndex = column;

	    var newRows = new Array();
	    for (j = 0; j < this.tbody[0].rows.length; j++) {
			newRows[j] = this.tbody[0].rows[j];
		}

		newRows.sort(sortfn);
		
		for (var i=0; i<sortRow.cells.length; i++) {
			var jpr_cell = sortRow.cells[i]
			if ( asc != null ) {
				if (this.HasClassName(jpr_cell, asc)) {
					this.RemoveClassName(jpr_cell, asc);
				}
			}
			if ( desc != null ) {
				if (this.HasClassName(jpr_cell, desc)) {
					this.RemoveClassName(jpr_cell, desc);
				}
			}
		}

		if (cell.getAttribute("sortdir") == 'down') {
			newRows.reverse();
			cell.setAttribute('sortdir','up');
			if ( asc != null ) {
				if (this.HasClassName(cell, asc)) {
					this.RemoveClassName(cell, asc);
				}
			}
			if ( desc != null ) {
				this.AddClassName(cell, desc, true);
			}
		} else {
			cell.setAttribute('sortdir','down');
			if ( desc != null ) {
				if (this.HasClassName(cell, desc)) {
					this.RemoveClassName(cell, desc);
				}
			}
			if ( asc != null ) {
				this.AddClassName(cell, asc, true);
			}
		}

		for (i=0;i<newRows.length;i++) {
			this.tbody[0].appendChild(newRows[i]);
		}

	}

	this.sortCaseInsensitive = function(a,b) {
		aa = thisObject.getInnerText(a.cells[thisObject.sortColumnIndex]).toLowerCase();
		bb = thisObject.getInnerText(b.cells[thisObject.sortColumnIndex]).toLowerCase();
		if (aa==bb) return 0;
		if (aa<bb) return -1;
		return 1;
	}

	this.sortDate = function(a,b) {
		aa = thisObject.getInnerText(a.cells[thisObject.sortColumnIndex]);
		bb = thisObject.getInnerText(b.cells[thisObject.sortColumnIndex]);
		date1 = aa.substr(6,4)+aa.substr(3,2)+aa.substr(0,2);
		date2 = bb.substr(6,4)+bb.substr(3,2)+bb.substr(0,2);
		if (date1==date2) return 0;
		if (date1<date2) return -1;
		return 1;
	}

	this.sortNumeric = function(a,b) {
		aa = parseFloat(thisObject.getInnerText(a.cells[thisObject.sortColumnIndex]));
		if (isNaN(aa)) aa = 0;
		bb = parseFloat(thisObject.getInnerText(b.cells[thisObject.sortColumnIndex]));
		if (isNaN(bb)) bb = 0;
		return aa-bb;
	}

	// define variables
	var thisObject = this;
	var sortSection = this.thead;

	// constructor actions
	if (!(this.tbody && this.tbody[0].rows && this.tbody[0].rows.length > 0)) return;

	if (sortSection && sortSection[0].rows && sortSection[0].rows.length > 0) {
		var sortRow = sortSection[0].rows[0];
	} else {
		return;
	}

	for (var i=0; i<sortRow.cells.length; i++) {
		sortRow.cells[i].sTable = this;
		sortRow.cells[i].onclick = function () {
			this.sTable.sort(this);
			return false;
		}
	}

}