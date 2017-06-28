$(document).ready(function() {
    $('.date-picker').datepicker({
        orientation: "right",
        startDate: "01/01/2010"
    });

    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": false,
        "positionClass": "toast-top-right",
        "preventDuplicates": true,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }

    function setBuyerData(row) {
        if(row.length !== 0) {
            $('#buyer-address').val(row.address);
            $('#buyer-zip').val(row.zipCode);
            $('#buyer-city').val(row.city);
            $('#buyer-vatId').val(row.vatId);
        }
    }

    function addAjaxSelectToElement(element, placeholder, url, additionalFunctionOnSelectedData) {
        var searchPhrase = null;
        var select2Element = null;
        var dataCollection = null;
        var isData = true;
        element.select2({
            placeholder: placeholder,
            minimumInputLength: 1,
            ajax: {
                url: url,
                dataType: 'json',
                type: "POST",
                quietMillis: 50,
                data: function (phrase) {
                    searchPhrase = phrase;
                    return {
                        phrase: phrase
                    };
                },
                results: function (data) {
                    dataCollection = data;
                    if(data.rowset.length === 0) {
                        isData = false;
                    } else {
                        isData = true;
                    }
                    return {
                        results: data.rowset
                    };
                }
            },
            formatResult: function (row) {
                var markup = '<table><tbody><tr><td><h5>'+row.name+'</h5></td></tr></tbody></table>';
                return markup;
            },
            formatSelection: function (row) {
                if(additionalFunctionOnSelectedData) {
                    additionalFunctionOnSelectedData(row);
                }
                return row.name;
            },
            dropdownCssClass: "bigdrop",
            escapeMarkup: function (m) {
                return m;
            }
        }).on("select2-open", function(e) {
            select2Element = e.currentTarget;
        }).on("select2-close", function(e) {
            if(!isData) {
                $(select2Element).select2('data', {id: 1, name: searchPhrase});
            }
        });
    }

    function calculatePaymentDate() {
        var issuedDate = $('#issued-date').val();
        var daysToPayment = parseInt($('#days-to-payment').val());
        if(daysToPayment.length !== 0) {
            $.post(getPaymentDateUrl,  { issuedDate: issuedDate, daysToPayment: daysToPayment }, function( data ) {
                if(data && data.length !== 0) {
                    if(data.success == true) {
                        $('#payment-date').val(data.date);
                    }
                }
            });
        }
    }

    function recalculateTableIndexes() {
        $('table#invoice-main-table tbody tr td.invoice-table-index').each( function( index, element ){
            $(this).text(index + 1);
        });
    }

    function getItemRowValues(element) {
        if(element.val() == null || element.val() == '') {
            element.val(0);
        }

        var itemQty = element.parent().parent('tr').find('input[data-name="item-qty"]').val().replace(/\,/g, '.');
        var singleItemNetValue = element.parent().parent('tr').find('input[data-name="item-net-value"]').val().replace(/\,/g, '.');
        var singleItemVatValue = element.parent().parent('tr').find('input[data-name="item-vat-value"]').val().replace(/\,/g, '.');

        if(element.data('name') == 'item-net-value' && singleItemNetValue.indexOf(".") < 0) {
            var itemNetValueForFormat = parseFloat(element.val());
            element.val(itemNetValueForFormat.toFixed(2));
        }

        if(element.data('name') == 'item-net-value' && singleItemNetValue.indexOf(".") >= 0) {
            element.val(singleItemNetValue);
        }

        var itemNetValue = parseFloat(singleItemNetValue * itemQty);
        var itemVatValue = parseFloat((itemNetValue * singleItemVatValue)/100);
        var itemValue = itemNetValue + itemVatValue;

        element.parent().parent('tr').find('td[data-name="item-net-value"]').text(itemNetValue.toFixed(2));
        element.parent().parent('tr').find('td[data-name="item-value"]').text(itemValue.toFixed(2));
    }

    function calculateTotalValues(invoiceCurrency) {
        if(typeof invoiceCurrency != 'undefined') {
            var totalNetValue = null;
            var totalValue = null;

            $('td[data-name="item-net-value"], td[data-name="item-vat-value"], td[data-name="item-value"]').each(function (index, element) {
                var value = parseFloat($(this).text());

                switch ($(this).data('name')) {
                    case 'item-net-value':
                        totalNetValue += value;
                        break;
                    case 'item-value':
                        totalValue += value;
                        break;
                }
            });

            $('.total-invoice-net-value').text(totalNetValue.toFixed(2) + ' ' + invoiceCurrency);
            $('.total-invoice-vat-value').text((totalValue - totalNetValue).toFixed(2) + ' ' + invoiceCurrency);
            $('.total-invoice-value').text(totalValue.toFixed(2) + ' ' + invoiceCurrency);
            convertAmountToWords($('.total-invoice-value').text());
        }
    }

    function getTotalValues() {
        var data = {};
        var totalNetValue = null;
        var totalValue = null;

        $('td[data-name="item-net-value"], td[data-name="item-vat-value"], td[data-name="item-value"]').each(function (index, element) {
            var value = parseFloat($(this).text());

            switch ($(this).data('name')) {
                case 'item-net-value':
                    totalNetValue += value;
                    break;
                case 'item-value':
                    totalValue += value;
                    break;
            }
        });

        data = {
            "totalNetValue": totalNetValue.toFixed(2),
            "totalVatValue": (totalValue - totalNetValue).toFixed(2),
            "totalValue": totalValue.toFixed(2)
        }

        if (!jQuery.isEmptyObject(data)) {
            return data;
        }
    }

    function convertAmountToWords(amount) {
        if(typeof amount != 'undefined' && typeof invoiceCurrency != 'undefined') {
            $.post(convertAmountToWordsUrl, {amount: amount, currency: invoiceCurrency}, function (data) {
                if (data && data.length !== 0) {
                    if (data.success == true) {
                        $('#amount-in-words').text(data.convertedAmount);
                    }
                }
            });
        }
    }

    function createService(invoiceKey) {
        var data = {};

        $('#invoice-main-table tbody tr').each(function(index, value) {
            if($(this).find('input[data-name="item-name"]').select2("data")) {
                var itemName = $(this).find('input[data-name="item-name"]').select2("data").name;
                var itemQty = $(this).find('input[data-name="item-qty"]').val().replace(/\,/g, '.');
                var itemUnit = $(this).find('input[data-name="item-unit"]').val();
                var unitaryNetValue = $(this).find('input[data-name="item-net-value"]').val().replace(/\,/g, '.');
                var unitaryVatValue = $(this).find('input[data-name="item-vat-value"]').val().replace(/\,/g, '.');

                var itemNetValue = parseFloat(unitaryNetValue * itemQty);
                var itemVatValue = parseFloat((itemNetValue * unitaryVatValue) / 100);
                var itemValue = itemNetValue + itemVatValue;

                if (
                    itemName.length !== 0 &&
                    itemQty.length !== 0 &&
                    itemUnit.length !== 0 &&
                    unitaryNetValue.length !== 0 &&
                    unitaryVatValue.length !== 0 &&
                    itemNetValue.length !== 0 &&
                    itemVatValue.length !== 0 &&
                    itemValue.length !== 0
                ) {
                    data[index] = {
                        "name": itemName,
                        "qty": itemQty,
                        "unit": itemUnit,
                        "unitaryNetValue": unitaryNetValue,
                        "unitaryVatValue": unitaryVatValue,
                        "netValue": itemNetValue,
                        "vat": itemVatValue,
                        "value": itemValue
                    }
                }
            }
        });

        if(!jQuery.isEmptyObject(data)) {
            $.post(createServiceUrl,  { data: data, invoiceKey: invoiceKey }, function( data ) {
                if(data && data.length !== 0) {
                    if(data.success == true) {
                        toastr.success('Usługa została zapisana pomyślnie');
                    }
                }
            });
        }
    }

    function createBuyer(invoiceKey) {
        if($('#buyer-name').select2("data")) {
            var data = {};

            var buyerName = $('#buyer-name').select2("data").name;
            var buyerAddress = $('#buyer-address').val();
            var buyerZip = $('#buyer-zip').val();
            var buyerCity = $('#buyer-city').val();
            var buyerVatId = $('#buyer-vatId').val();

            if (
                buyerName.length !== 0 &&
                buyerVatId.length !== 0 &&
                buyerAddress.length !== 0 &&
                buyerCity.length !== 0 &&
                buyerZip.length !== 0
            ) {
                data = {
                    "name": buyerName,
                    "vatId": buyerVatId,
                    "address": buyerAddress,
                    "city": buyerCity,
                    "zipCode": buyerZip,
                    "country": "Polska"
                }
            }

            if (!jQuery.isEmptyObject(data)) {
                $.post(createBuyerUrl, { data: data, invoiceKey: invoiceKey }, function (data) {
                    if (data && data.length !== 0) {
                        if (data.success == true) {
                            toastr.success('Nabywca został zapisany pomyślnie');
                        }
                    }
                });
            }
        }
    }

    function createInvoice() {
        var data = {};
        var totalValueData = getTotalValues();
        var issuedDate = $('#issued-date').val();
        var sellDate = $('#sell-date').val();
        var paymentDate = $('#payment-date').val();
        var invoiceFullNumber = $('#invoice-full-number').text();
        var totalVatValue = $('.total-invoice-vat-value').text();
        var totalNetValue = $('.total-invoice-net-value').text();
        var totalValue = $('.total-invoice-value').text();
        var invoiceComments = $('#invoice-comments').val();
        var recipientName = "Marcin Walas";

        if (
            issuedDate.length !== 0 &&
            sellDate.length !== 0 &&
            paymentDate.length !== 0 &&
            invoiceFullNumber.length !== 0
        ) {
            data = {
                "fullNumber": invoiceFullNumber,
                "vat": totalValueData.totalVatValue,
                "netValue": totalValueData.totalNetValue,
                "value": totalValueData.totalValue,
                "comments": invoiceComments,
                "recipientName": recipientName,
                "dateIssue": issuedDate,
                "dateSell": sellDate,
                "datePayment": paymentDate
            }
        }

        if (!jQuery.isEmptyObject(totalValueData) && !jQuery.isEmptyObject(data)) {
            $.post(createInvoiceUrl, {data: data}, function (data) {
                if (data && data.length !== 0) {
                    if (data.success == true) {
                        createService(data.key);
                        createBuyer(data.key);
                        toastr.success('Faktura została wystawiona pomyślnie');
                    }
                }
            });
        }
    }

    $('#days-to-payment').val(7);

    $('#days-to-payment').live("change", function() {
        calculatePaymentDate();
    });

    $('#issued-date').live("change", function() {
        calculatePaymentDate();
    });

    $('.show-original-copy').live('click', function() {
        $('.show-original-copy').removeClass('red');
        $('.show-original-copy').addClass('grey');
        $(this).removeClass('grey');
        $(this).addClass('red');

        if($(this).data('key') == 1) {
            $('#original-copy-txt').show();
        } else {
            $('#original-copy-txt').hide();
        }
    });

    var invoiceItemRow =
        '<tr> ' +
        '<td class="invoice-table-index text-vcenter-center"></td> ' +
        '<td>' +
        '<div class="select2-container form-control select2"> ' +
        '<input class="select2-focusser select2-offscreen" data-name="item-name" type="text" aria-haspopup="true" role="button" aria-labelledby="select2-chosen-31" style="width: 100% !important;"> ' +
        '</div> ' +
        '</td> ' +
        '<td class="hidden-480"> ' +
        '<input type="text" class="form-control" data-name="item-qty"> ' +
        '</td> ' +
        '<td class="hidden-480"> ' +
        '<input type="text" class="form-control" data-name="item-unit"> ' +
        '</td> ' +
        '<td class="hidden-480"> ' +
        '<input type="text" class="form-control" data-name="item-net-value"> ' +
        '</td> ' +
        '<td class="hidden-480"> ' +
        '<input type="text" class="form-control" data-name="item-vat-value"> ' +
        '</td> ' +
        '<td class="hidden-480 text-vcenter" data-name="item-net-value"></td> ' +
        '<td class="text-vcenter" data-name="item-value"></td> ' +
        '<td class="text-align-center"> ' +
        '<a href="#" class="add-new-invoice-record btn btn-icon-only green"><i class="fa fa-plus"></i></a> ' +
        '<a href="#" class="remove-invoice-record btn btn-icon-only red"><i class="fa fa-times"></i></a> ' +
        '</td> ' +
        '</tr>';

    var invoiceCurrency = $('.invoice-currency').data('name');

    $('.invoice-currency').live("click", function() {
        var prevCurrency = $('.invoice-currency.green').data('name');
        var exchangedValue = null;

        invoiceCurrency = $(this).data('name');

        $('.invoice-currency').removeClass('green');
        $('.invoice-currency').addClass('grey');
        $(this).removeClass('grey');
        $(this).addClass('green');

        $('#invoice-main-table tbody tr input[data-name="item-net-value"]').each(function() {
            var element = $(this);
            var value = $(this).val();
            if(value.length !== 0 && rateUSD != null && rateEUR != null) {
                value = parseFloat(value);
                rateUSD = parseFloat(rateUSD);
                rateEUR = parseFloat(rateEUR);
                if(invoiceCurrency == 'PLN') {
                    switch(prevCurrency) {
                        case 'USD':
                            exchangedValue = value*rateUSD;
                            break;
                        case 'EUR':
                            exchangedValue = value*rateEUR;
                            break;
                    }
                }
                if(invoiceCurrency == 'USD') {
                    switch(prevCurrency) {
                        case 'PLN':
                            exchangedValue = value/rateUSD;
                            break;
                        case 'EUR':
                            exchangedValue = (value*rateEUR)/rateUSD;
                            break;
                    }
                }
                if(invoiceCurrency == 'EUR') {
                    switch(prevCurrency) {
                        case 'PLN':
                            exchangedValue = value/rateEUR;
                            break;
                        case 'USD':
                            exchangedValue = (value*rateUSD)/rateEUR;
                            break;
                    }
                }
                element.val(exchangedValue.toFixed(2));
                getItemRowValues(element);
                calculateTotalValues(invoiceCurrency);
            }
        });
    });

    $('input[data-name="item-qty"], input[data-name="item-net-value"], input[data-name="item-vat-value"]').live("change", function() {
        getItemRowValues($(this));
        calculateTotalValues(invoiceCurrency);
    });

    $('a.add-new-invoice-record').live("click", function() {
        Metronic.scrollTo($('#invoice-main-table'), -200);
        $(invoiceItemRow).insertAfter($(this).parent().parent('tr'));
        addAjaxSelectToElement($(this).parent().parent('tr').next('tr').find('input[data-name="item-name"]'), "Szukaj usługi...", getServicesUrl);
        recalculateTableIndexes();
        return false;
    });

    $('a.remove-invoice-record').live("click", function() {
        Metronic.scrollTo($('#invoice-main-table'), -200);
        var target = $(this);
        var countTableRows = $('table#invoice-main-table tbody tr').length;
        if(countTableRows > 1) {
            $(this).parent().parent('tr').remove();
        }
        recalculateTableIndexes();
        calculateTotalValues(invoiceCurrency);
        return false;
    });

    addAjaxSelectToElement($('input[data-name="item-name"]'), "Szukaj usługi...", getServicesUrl);
    addAjaxSelectToElement($('#buyer-name'), "Nazwa", getBuyersUrl, setBuyerData);

    $('#save-changes').on("click", function(element){
        if(element.handled !== true) {
            calculatePaymentDate();
            createInvoice();
            toastr.success('Zmiany zostały zapisane pomyślnie');

            element.handled = true;
        }
    });
});