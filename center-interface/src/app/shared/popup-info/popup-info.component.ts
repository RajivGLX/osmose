import { Component, Input } from '@angular/core';
import { MatTooltipModule } from '@angular/material/tooltip';

@Component({
    selector: 'app-popup-info',
    standalone: true,
    imports: [MatTooltipModule],
    templateUrl: './popup-info.component.html',
    styleUrl: './popup-info.component.sass'
})
export class PopupInfoComponent {
    @Input() message: string = '';
    @Input() position: 'above' | 'below' | 'left' | 'right' = 'above';
    @Input() tooltipClass: string = 'custom-tooltip';
}
