import { ComponentFixture, TestBed } from '@angular/core/testing';

import { VueDetailleeDemandeErpComponent } from './vue-detaillee-demande-erp.component';

describe('VueDetailleeDemandeErpComponent', () => {
    let component: VueDetailleeDemandeErpComponent;
    let fixture: ComponentFixture<VueDetailleeDemandeErpComponent>;

    beforeEach(async () => {
        await TestBed.configureTestingModule({
            imports: [VueDetailleeDemandeErpComponent]
        })
            .compileComponents();

        fixture = TestBed.createComponent(VueDetailleeDemandeErpComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });
});
