import { TestBed } from '@angular/core/testing';

import { CenterFormService } from './center-form.service';

describe('CenterFormServicesService', () => {
    let service: CenterFormService;

    beforeEach(() => {
        TestBed.configureTestingModule({});
        service = TestBed.inject(CenterFormService);
    });

    it('should be created', () => {
        expect(service).toBeTruthy();
    });
});
