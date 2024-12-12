BX.namespace("Dis.MecVotes");

BX.Dis.MecVotes = new function () {
    this.AjaxPath = {
        'setLike': '/local/themes/dis.mecvotes/ajax/setLike.php',
        'getContentStat': '/local/themes/dis.mecvotes/ajax/getContentStat.php',
    };

    this.setAjaxPath = function (AjaxPath) {
        this.AjaxPath = AjaxPath;
    };

    this.setLike = function (iContentId, iContentType, oCallback) {
        var callback = this.isFunction(oCallback) ? oCallback : function () {};
        BX.ajax.post(this.AjaxPath.setLike, {
            'sessid': BX.bitrix_sessid(),
            'iContentId': iContentId,
            'iContentType': iContentType
        }, callback);
    };

    this.getContentStat = function (iContentId, iContentType, oCallback) {
        var callback = this.isFunction(oCallback) ? oCallback : function () {};
        BX.ajax.post(this.AjaxPath.getContentStat, {
            'sessid': BX.bitrix_sessid(),
            'iContentId': iContentId,
            'iContentType': iContentType
        }, callback);
    };

    this.isFunction = function (functionToCheck) {
        return functionToCheck && {}.toString.call(functionToCheck) === '[object Function]';
    }

};