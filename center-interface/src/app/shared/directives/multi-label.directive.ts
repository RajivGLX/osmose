import {Directive, Input, ElementRef, Renderer2} from '@angular/core';

@Directive({
    selector: '[appMultiLabel]',
    standalone: true,
})
export class MultiLabelDirective {
    @Input() items: any[] = [];

    constructor(
        private el: ElementRef, 
        private renderer: Renderer2
    ) {}



    ngOnChanges() {
        let label = '';
        const maxItemsToShow = 3;
        const maxLength = 25;

        for (let i = 0; i < Math.min(this.items.length, maxItemsToShow); i++) {
            const itemName = this.items[i].name;
            if (itemName.length > maxLength) {
                label += `${itemName.slice(0, maxLength)}...`;
            } else {
                label += itemName;
            }
            if (i < Math.min(this.items.length, maxItemsToShow) - 1) {
                label += ', ';
            }
        }

        if (this.items.length > maxItemsToShow) {
            label += ` + ${this.items.length - maxItemsToShow}`;
        }

        this.renderer.setProperty(this.el.nativeElement, 'innerText', label);
    }

}
