import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CenterCalendarComponent } from './center-calendar.component';

describe('CenterCalendarComponent', () => {
  let component: CenterCalendarComponent;
  let fixture: ComponentFixture<CenterCalendarComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [CenterCalendarComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(CenterCalendarComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
