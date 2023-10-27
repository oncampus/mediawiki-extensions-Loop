var choosenTags = [];
var titleHTMLs = {};
var tagHTMLs = {};
var tagMap = {};
var modifiedTagMap = {};
var checkBoxes = [];
var filteredListAnchor;

$(document).ready(function () {
  filteredListAnchor = document.getElementById("filtered-tag-table");
  /* Add all tags into an internal array */
  var table = document.getElementById("tag_table");

  table.childNodes.forEach((row) => {
    var chapterHTML = row.getElementsByTagName("a")[0];
    var chapterName = chapterHTML.innerHTML;

    // Copy chapter titles into dict
    titleHTMLs[chapterName] = chapterHTML;
    // Create clean array to add onto
    tagMap[chapterName] = [];
    modifiedTagMap[chapterName] = [];
    var listItems = row.getElementsByTagName("li");

    for (item of listItems) {
      var itemName = item.innerHTML;
      // Add item to tagMap
      tagMap[chapterName].push(itemName);
      // Copy htmls into dict
      tagHTMLs[itemName] = item;
    }
  });

  /* Get checkboxes into an internal Array */
  checkBoxes = document
    .getElementById("tag-filter")
    .querySelectorAll("[name='filter']");

  /* Add Eventlisteners to each Checkbox */
  checkBoxes.forEach((checkbox) => {
    checkbox.addEventListener("change", function () {
      var id = checkbox.id;
      if (this.checked) {
        // add to filter
        choosenTags.push(id);
        addToFiltered(id);
      } else {
        // remove from filter
        const index = choosenTags.indexOf(id);
        if (index > -1) {
          // only splice array when item is found
          choosenTags.splice(index, 1); // 2nd parameter means remove one item only
          removeFromFiltered(id);
        }
      }
    });
  });

  /* Add Button Events */
  // toggle all
  document.getElementById("tag-filter-toggle-all").addEventListener(
    "click",
    function () {
      checkBoxes.forEach((checkbox) => {
        if (checkbox.checked == false) {
          checkbox.checked = true;
          addToFiltered(checkbox.id);
        }
      });
    },
    false
  );
  // toggle none
  document.getElementById("tag-filter-toggle-none").addEventListener(
    "click",
    function () {
      checkBoxes.forEach((checkbox) => {
        if (checkbox.checked == true) {
          checkbox.checked = false;
          removeFromFiltered(checkbox.id);
        }
      });
    },
    false
  );
});

function addToFiltered(tagToAdd) {
  Object.keys(tagMap)
    .filter((key) => tagMap[key].includes(tagToAdd))
    .forEach((key) => {
      if (modifiedTagMap[key].length == 0) {
        // add new row
        var newRow = document.createElement("tr");
        newRow.id = "row-" + key.replace(/\W/g, "_");
        newRow.className = "ml-1 pb-1";
        newRow.scope = "row";
        // prepare the title
        var title = document.createElement("td");
        title.id = "title-" + key;
        title.class = "pl-1 pr-1";
        title.scope = "col";
        // prepare the actual link
        var titleSpan = document.createElement("span");
        // append all into a valid HTMLElement
        titleSpan.appendChild(titleHTMLs[key].cloneNode(true));
        title.appendChild(titleSpan);
        newRow.appendChild(title);
        // prepare the taglist
        var listCol = document.createElement("td");
        var list = document.createElement("ul");
        // clone the li
        var listEntry = tagHTMLs[tagToAdd].cloneNode(true);
        // give it individual id, to find and remove it easier later on
        listEntry.id = "tag-" + tagToAdd.replace(/\W/g, "_");
        // add just this one entry for now
        list.appendChild(listEntry);
        // append all as valid html
        listCol.appendChild(list);
        newRow.appendChild(listCol);
        // Add all into the table
        filteredListAnchor.appendChild(newRow);
      } else {
        // get the Node
        var rowNode = filteredListAnchor.querySelector(
          "#row-" + key.replace(/\W/g, "_")
        );
        // copy the li
        var listEntry = tagHTMLs[tagToAdd].cloneNode(true);
        // give it individual id, to find and remove it easier later on
        listEntry.id = "tag-" + tagToAdd.replace(/\W/g, "_");
        // add li to list
        rowNode.querySelector("ul").appendChild(listEntry);
      }
      modifiedTagMap[key].push(tagToAdd);
    });
}
function removeFromFiltered(tagToRemove) {
  Object.keys(tagMap)
    .filter((key) => tagMap[key].includes(tagToRemove))
    .forEach((key) => {
      const index = modifiedTagMap[key].indexOf(tagToRemove);
      if (index > -1) {
        // only splice array when item is found, tho this should always happen
        modifiedTagMap[key].splice(index, 1); // 2nd parameter means remove one item only
      }
      // Check if Title still holds any filtered tags
      if (modifiedTagMap[key].length == 0) {
        // remove row
        filteredListAnchor
          .querySelector("#row-" + key.replace(/\W/g, "_"))
          .remove();
      } else {
        // remove entry from list
        var rowNode = filteredListAnchor.querySelector(
          "#row-" + key.replace(/\s/g, "_")
        );
        rowNode
          .querySelector("#tag-" + tagToRemove.replace(/\W/g, "_"))
          .remove();
      }
    });
}
