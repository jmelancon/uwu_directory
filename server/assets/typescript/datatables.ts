import DataTable, {Config} from "datatables.net-bs5";
import $ from "jquery";

export function prepDataTable(tableId: string, config: Config){
    new DataTable(tableId, config);

    // noinspection CssInvalidHtmlTagReference
    const table = $(`${tableId}_wrapper`);

    // Filter elements (Upper-right search box)
    const filters = table.find(".dt-search");
    const filterInput = filters.find("input");
    const filterLabel = filters.find("label");

    // Limit element (Upper left)
    const limit = table.find(".dt-length");

    // Rows & columns. Remove a shitload of classes from cols while we're at it.
    const layoutRow = table.find(".row");
    const layoutCells = layoutRow.find(".col-md-auto").removeClass("col-md-auto d-md-flex justify-content-between align-items-center mw-auto me-auto");

    // Tweak filter classes
    filters.addClass("form-floating").removeClass("dt-search");
    filterLabel.addClass("form-label");
    filterInput.addClass("form-control").removeClass("dt-input");
    $(layoutCells[1]).addClass("offset-lg-6")

    // Tweak limit classes
    limit.addClass("form-floating")
    limit.find("select").addClass("form-select").removeClass("form-select-sm");
    limit.parent().parent().addClass("row-gap-3")

    // Tweak rows & columns
    layoutRow.addClass("row")
    layoutCells.addClass("col-lg-3")

    // Pull paginator right
    $(".col-lg-3:has(.dt-paging)").addClass("col-lg-6 align-items-lg-end align-items-center").removeClass("col-lg-3");

    // Tweak filter footnote
    $(".col-lg-3:has(.dt-info)").addClass("col-lg-6 mt-2 form-label").removeClass("col-lg-3");

    // Initialize input components
    filterInput.insertBefore(filterLabel);
}

window.addEventListener("load",(_e) => {
    $.extend(
        DataTable.ext.classes.paging,
        DataTable.ext.classes.paging,
        {
            button: "btn btn-link",
            nav: "btn-group shadow-0 float-lg-end pt-1"
        }
    );
});