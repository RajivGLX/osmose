import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CenterCreateComponent } from './center-create.component';

describe('CenterCreateComponent', () => {
  let component: CenterCreateComponent;
  let fixture: ComponentFixture<CenterCreateComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [CenterCreateComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(CenterCreateComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
