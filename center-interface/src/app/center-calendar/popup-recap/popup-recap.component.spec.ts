import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PopupRecapComponent } from './popup-recap.component';

describe('PopupRecapComponent', () => {
  let component: PopupRecapComponent;
  let fixture: ComponentFixture<PopupRecapComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [PopupRecapComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(PopupRecapComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
