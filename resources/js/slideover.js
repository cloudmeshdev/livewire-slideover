window.LivewireUISlideover = () => {
    return {
        show: false,
        activeComponents: [],
        foregroundComponentId: null,
        
        closeSlideover(force = false, skipPreviousSlideovers = 0, destroySkipped = false) {
            console.log('closeSlideover');

            if (this.show === false) {
                return;
            }

            if (!this.foregroundComponentId) {
                return;
            }

            // devo dispatchare eventi addizionali quando chiudo?
            if (this.getComponentAttributeById(this.foregroundComponentId, 'dispatchCloseEvent') === true) {
                const componentName = this.$wire.get('components')[this.foregroundComponentId].name;
                Livewire.emit('slideoverClosed', componentName);
            }

            // devo anche distruggere il componente quando chiudo?
            if (this.getComponentAttributeById(this.foregroundComponentId, 'destroyOnClose') === true) {
                Livewire.emit('destroyComponent', this.foregroundComponentId);
            }

            // togli l'id del componente appena chiuso
            this.activeComponents.pop();
            this.foregroundComponentId = null;

            // pesca l'id del componente precedente (se presente)
            let latestActiveComponentId = this.activeComponents[this.activeComponents.length - 1];

            if (latestActiveComponentId) {
                setTimeout(() => {
                    this.foregroundComponentId = latestActiveComponentId;
                    this.setForegroundComponent(latestActiveComponentId);
                }, 300);
            } else {
                this.foregroundComponentId = null;                
                this.$wire.resetState();
                this.setShowPropertyTo(false);
            }
        },

        getComponentIdByIndex(index) {
            return this.activeComponents[index];
        },
        
        getComponentAttributeById(id, key) {

            if (this.$wire.get('components')[id] !== undefined) {
                return this.$wire.get('components')[id]['slideoverAttributes'][key];
            }
        },
        
        setShowPropertyTo(show) {
            this.show = show;

            if (show) {
                document.body.classList.add('overflow-y-hidden');
            } else {
                document.body.classList.remove('overflow-y-hidden');
            }
        },

        closeSlideoverOnEscape(trigger) {
            if (this.getComponentAttributeById(this.foregroundComponentId, 'closeOnEscape') === false) {
                return;
            }

            let force = this.getComponentAttributeById(this.foregroundComponentId, 'closeOnEscapeIsForceful') === true;
            this.closeSlideover(force);
        },

        closeSlideoverOnClickAway(trigger) {
            if (this.getComponentAttributeById(this.foregroundComponentId, 'closeOnClickAway') === false) {
                return;
            }

            this.closeSlideover(true);
        },

        setForegroundComponent(id, skip = false) {
            console.log('setForegroundComponent', id);
            
            // this.setShowPropertyTo(true);
            this.show = true;

            // evita di mostrare 2 volte lo stesso componente
            if (this.activeComponents.includes(id)) {
                return;
            }

            this.activeComponents.push(id);
            this.foregroundComponentId = id;
        },


        init() {
            // resta in ascolto del 'closeSlideover'
            Livewire.on('closeSlideover', 
                (force = false, skipPreviousSlideovers = 0, destroySkipped = false) => {
                    this.closeSlideover(force, skipPreviousSlideovers, destroySkipped);
                }
            );

            Livewire.on('activeSlideoverComponentChanged', (id) => {
                this.setForegroundComponent(id);
            });
        },

    };
}
