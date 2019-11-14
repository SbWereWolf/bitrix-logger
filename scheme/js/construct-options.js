selectWithTypes = {
    get: function () {
        let html = '';
        $.each(names, function (index, value) {
            html = `${html}<option value="${index}">${value}</option>`
        });
        return html;
    }
};