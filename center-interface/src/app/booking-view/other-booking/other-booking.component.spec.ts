import { ComponentFixture, TestBed } from '@angular/core/testing';

import { OtherBookingComponent } from './other-booking.component';

describe('OtherBookingComponent', () => {
  let component: OtherBookingComponent;
  let fixture: ComponentFixture<OtherBookingComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [OtherBookingComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(OtherBookingComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
