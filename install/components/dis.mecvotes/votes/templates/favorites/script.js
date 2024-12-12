;(function(window) {
    if (window.JCDisMecVote)
        return;

    window.JCDisMecVote = function (params) {

        this.voteType = '';
        this.isColorInvert = false;
        this.voteId = '';
        this.entityId = '';
        this.isActive = false;
        this.countVotes = 0;
        this.elementId = '';
        this.elementSignedParams = '';

        this.containerId = '';
        this.buttonId = '';
        this.counterId = '';

        this.containerObj = null;
        this.buttonObj = null;
        this.counterObj = null;

        if (BX.type.isPlainObject(params)) {
            if (BX.type.isNotEmptyString(params.voteType))
                this.voteType = params.voteType;
            if (BX.type.isBoolean(params.isColorInvert))
                this.isColorInvert = params.isColorInvert;
            if (BX.type.isNotEmptyString(params.voteId))
                this.voteId = params.voteId;
            if (BX.type.isNotEmptyString(params.entityId))
                this.entityId = params.entityId;
            if (BX.type.isBoolean(params.isActive))
                this.isActive = params.isActive;
            if (BX.type.isInteger(params.countVotes))
                this.countVotes = params.countVotes;
            if (BX.type.isNotEmptyString(params.elementId))
                this.elementId = params.elementId;
            if (BX.type.isNotEmptyString(params.elementSignedParams))
                this.elementSignedParams = params.elementSignedParams;

            if (BX.type.isNotEmptyString(params.containerId))
                this.containerId = params.containerId;
            if (BX.type.isNotEmptyString(params.buttonId))
                this.buttonId = params.buttonId;
            if (BX.type.isNotEmptyString(params.counterId))
                this.counterId = params.counterId;
        }

        BX.namespace('Dis.MecVotes.Components.SignedParameters');
        BX.Dis.MecVotes.Components.SignedParameters[this.elementId] = this.elementSignedParams;

        BX.ready(BX.proxy(this.init, this));
    };

    window.JCDisMecVote.prototype.init = function () {

        if (BX.type.isNotEmptyString(this.containerId))
            this.containerObj = BX(this.containerId);

        if (BX.type.isNotEmptyString(this.buttonId))
            this.buttonObj = BX(this.buttonId);

        if (BX.type.isNotEmptyString(this.counterId))
            this.counterObj = BX(this.counterId);

        /*if (BX.type.isElementNode(this.buttonObj)) {
            BX.bind(this.buttonObj, 'click', BX.proxy(this.setStateVote, this));
        }*/

        if (BX.type.isElementNode(this.containerObj)) {
            this.setHintText();
        }
    };

    window.JCDisMecVote.prototype.setStateVote = function(event)
    {
        try { event.preventDefault(); }
        catch (e) {}

        var self = this;

        BX.ajax.runComponentAction('dis.mecvotes:votes',
            "setVote", {
                mode: 'class',
                data: {
                    sSignedParameters: BX.Dis.MecVotes.Components.SignedParameters[self.elementId]
                },
            })
            .then(function (response) {
                if (response.status === 'success') {
                    if (response.data === false){
                        alert('Для голосования необходимо авторизироваться');
                    } else {
                        self.getContentStatLikes(BX.Dis.MecVotes.Components.SignedParameters[self.elementId])
                    }
                }
            });
    };

    window.JCDisMecVote.prototype.setHintText = function(event)
    {
        try { event.preventDefault(); }
        catch (e) {}

        var self = this;

        var textHint = self.isActive ? "Убрать из избранного" : "Добавить в избранное";

        BX.UI.Hint.hide(self.buttonObj);
        self.buttonObj.removeAttribute('data-hint-init');
        self.buttonObj.setAttribute('data-hint', textHint);
        BX.UI.Hint.init(self.containerObj);
    };

    window.JCDisMecVote.prototype.getContentStatLikes = function (eID)
    {
        var self = this;
        BX.ajax.runComponentAction('dis.mecvotes:votes',
            'getContentStat', {
                mode: 'class',
                data: {
                    sSignedParameters: eID
                },
            })
            .then(function (response) {

                if (response.status === 'success') {
                    try {
                        var elStatBar = document.querySelector('[data-element="' + response.data.CONTENT_ID + ',' + response.data.CONTENT_TYPE + '"]');
                        var elVoteCounter = elStatBar.querySelector('.js-vote-counter');
                        var elVoteAction = elStatBar.querySelector('.js-vote-action');

                        if (response.data.STAT === null) {
                            elVoteCounter.innerText = '0';
                            elVoteAction.classList.remove('is-active');
                            self.isActive = false;
                            self.setHintText();
                            return;
                        }

                        elVoteCounter.innerText = response.data.STAT.COUNT_VOTE;

                        if (response.data.STAT.IS_VOTED) {
                            elVoteAction.classList.add('is-active');
                            self.isActive = true;
                            self.setHintText();
                        } else {
                            elVoteAction.classList.remove('is-active');
                            self.isActive = false;
                            self.setHintText();
                        }
                    }
                    catch (e) {}
                }
            });
    };

})(window);