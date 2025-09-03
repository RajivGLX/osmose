import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatSelectModule } from '@angular/material/select';
import { RouterLink } from '@angular/router';
import { OverviewService } from './service/overview.service';

@Component({
    selector: 'app-over-view',
    standalone: true,
    templateUrl: './over-view.component.html',
    styleUrl: './over-view.component.sass',
    imports: [
        CommonModule, 
        FormsModule, 
        ReactiveFormsModule,
        MatFormFieldModule,
        MatSelectModule, 
        RouterLink, 
        ]
})
export class OverViewComponent implements OnInit {

    test = 1
    testStatus = 'violet'

    switchView: 'overview' = 'overview';

    constructor(
        private overViewService: OverviewService,
    ) { }

    ngOnInit(): void {

    }

    changeView(view: 'overview') {
        this.switchView = view
    }

}
