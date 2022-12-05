/**************************** */
/* ADMIN TEMPLATES            */
/**************************** */
import $, { holdReady } from 'jquery';

var AdminTemplate = {
    initialize: function() {
        this.entity = $('.js-index-container').data("entity");
        this.renderTableHeader;
        this.renderTableContent;
        this.renderRow;
    },

    renderTable: function(items, offset, itemsPerPage, direction) {
        if (direction == "sort") {
            direction = "reverse"
        } else {
            direction = "sort"
        }
        return this.renderTableHeader(items, direction) +
            this.renderTableContent(items, offset, itemsPerPage);
    },
    renderTableHeader: function(items, direction) {
        var theadContent = `<thead class="main-red"><tr>`;
        Object.keys(items[0]).map(property => {
            theadContent += `<th><a href="#" class="sort-column" data-direction="${direction}">${property}</a></th>`;
        });
        return theadContent += `<th><i class="fa fa-eye"></i></th>
                                <th><i class="fa fa-edit"></i></th>
                                <th><i class="fa fa-trash"></i></th></tr></thead>`;

    },
    renderTableBody: function(items, offset, itemsPerPage) {
        var tableBody = `<tbody>` + this.renderTableContent(items, offset, itemsPerPage) + `</tbody>`;
        return tableBody;
    },
    renderTableContent: function(items, offset, itemsPerPage) {
        var showItems = items.slice(offset, (offset + itemsPerPage));
        var itemsHtml = ``;
        showItems.map(item => {
            itemsHtml += this.renderRow(item);
        });
        return itemsHtml;
    },
    renderRow: function(item) {
        //
        var rowContent = `<tr>`;
        for (const property in item) {
            if (property == 'totalPath') {
                rowContent += this.renderThumbnail(item[property])
            } else {
                rowContent += this.renderCell(item[property])
            }
        }
        return rowContent +=
            this.renderShowCell('/crud/' + this.entity, item.id) +
            this.renderEditCell('/crud/' + this.entity, item.id) +
            this.renderDeleteCell('/crud/' + this.entity, item.id) +
            `</tr>`;
    },
    renderThumbnail: function(url) {
        return `<td><img src="${url}" width="50" height="50"></td>`;
    },
    renderCell: function(item) {
        if (typeof item === 'object' && item !== null) {
            var itemList = "<ul>";
            Object.values(item).map(
                function(value) {
                    itemList += "<li>";
                    itemList += value;
                    itemList += '</li>';
                });
            itemList += "</ul>";
            return `<td>${itemList}</td>`;
        } else {
            return `<td>${item}</td>`;
        }
    },
    renderShowCell: function(baseUrl, id) {
        return `
            <td>
                <a href="${baseUrl}/${id}" class="btn btn-primary">
                    <i class="fa fa-eye"></i>
                </a>
            </td>`;
    },
    renderEditCell: function(baseUrl, id) {

        return `
            <td>
                <a href="${baseUrl}/${id}/edit" class="btn btn-info">
                    <i class="fa fa-edit"></i>
                </a>
            </td>`;
    },
    renderDeleteCell: function(baseUrl, id) {
        return `
            <td>
                <a href="#" 
                    class="btn btn-danger js-delete-item"
                    data-url="${baseUrl}/${id}">
                    <i class="fa fa-trash"></i>
                </a>
            </td>`;
    },
    renderPagination: function(count, page) {
        /*  */
        var pages = Math.ceil(count / 10);
        /*  */
        var pageLinks = '';
        var i;
        var isActive = '';
        if (count > 10) {
            if (pages < 20) {
                for (i = 1; i <= pages; i++) {
                    if (page == i) {
                        isActive = ` active`;
                    } else {
                        isActive = ``;
                    }
                    pageLinks += `<li class="page-item` +
                        isActive +
                        `"><span class="page-link">` + i + `</span></li>`;
                }
            }
            if (pages >= 20) {
                var interval = 10;
                var bottom = 0;
                if (page <= (interval / 2)) {
                    bottom = 0;
                } else if (page >= (pages - (interval / 2))) {
                    bottom = pages - interval;
                } else {
                    bottom = page - (interval / 2);
                }
                /* 
                
                 */
                for (i = bottom + 1; i <= (bottom + interval); i++) {
                    if (page == i) {
                        isActive = ` active`;
                    } else {
                        isActive = ``;
                    }
                    pageLinks += `<li class="page-item` +
                        isActive +
                        `"><span class="page-link">` + i + `</span></li>`;
                }
            }
            return `
                    <nav>
                        <ul class="pagination">
                            <li class="page-item"> 
                                <span class="page-link">
                                    Anterior
                                </span> 
                            </li>${pageLinks}<li class="page-item"> 
                        <span class="page-link">
                            Siguiente
                        </span> 
                    </li>
                    </ul>
                    </nav>`;
        } else {
            return ``;
        }
    },
}

export default AdminTemplate;