import { Injectable } from '@angular/core';
import { BreakpointObserver, Breakpoints } from '@angular/cdk/layout';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { Responsive } from '../interface/responsive.interface';

@Injectable({
    providedIn: 'root'
})
export class BreakpointService {
    breakpoints$: Observable<Responsive>

    constructor(
        private breakpointObserver: BreakpointObserver
    ) {
        this.breakpoints$ = this.breakpointObserver.observe([
            Breakpoints.XSmall,
            Breakpoints.Small,
            Breakpoints.Medium,
            Breakpoints.Large,
            Breakpoints.XLarge,
        ]).pipe(
            map(result => {
                return {
                    xSmall: result.breakpoints[Breakpoints.XSmall],
                    small: result.breakpoints[Breakpoints.Small],
                    medium: result.breakpoints[Breakpoints.Medium],
                    large: result.breakpoints[Breakpoints.Large],
                    xLarge: result.breakpoints[Breakpoints.XLarge],
                };
            })
        );
    }
}