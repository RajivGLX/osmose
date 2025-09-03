import { Component, Input, OnInit } from '@angular/core';
import { Patient } from '../../interface/patient.interface';


@Component({
    selector: 'app-folder-patient',
    standalone: true,
    imports: [],
    templateUrl: './folder-patient.component.html',
    styleUrl: './folder-patient.component.sass'
})
export class FolderPatientComponent implements OnInit {

    @Input() patient!: Patient

    constructor() {
    }

    ngOnInit(): void {
        
    }

}
