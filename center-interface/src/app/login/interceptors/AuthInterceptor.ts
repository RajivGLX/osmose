import { HttpEvent, HttpHandlerFn, HttpInterceptorFn, HttpRequest } from "@angular/common/http";
import { Observable, startWith } from 'rxjs';
import { environment } from "../../../environment/environment";

export const AuthInterceptor: HttpInterceptorFn = (req: HttpRequest<any>, next: HttpHandlerFn): Observable<HttpEvent<any>> => {

    const token = sessionStorage.getItem('token-auth');

    if (req.url !== environment.apiURL + '/api/login_check' && req.url !== environment.apiURL + '/register' && !req.url.startsWith('http://localhost:52848')) {
        const authReq = req.clone({
            setHeaders: {
                Authorization: `Bearer ${token}`,
            }
        });
        return next(authReq);
    }

    if (req.url.startsWith('http://localhost:52848')) {
        const authReq = req.clone({
            setHeaders: {
                Authorization: `Bearer eyJhbGciOiJIUzI1NiJ9.eyJtZXJjdXJlIjp7InB1Ymxpc2giOlsiKiJdLCJzdWJzY3JpYmUiOlsiaHR0cHM6Ly9leGFtcGxlLmNvbS9teS1wcml2YXRlLXRvcGljIiwie3NjaGVtZX06Ly97K2hvc3R9L2RlbW8vYm9va3Mve2lkfS5qc29ubGQiLCIvLndlbGwta25vd24vbWVyY3VyZS9zdWJzY3JpcHRpb25zey90b3BpY317L3N1YnNjcmliZXJ9Il0sInBheWxvYWQiOnsidXNlciI6Imh0dHBzOi8vZXhhbXBsZS5jb20vdXNlcnMvZHVuZ2xhcyIsInJlbW90ZUFkZHIiOiIxMjcuMC4wLjEifX19.KKPIikwUzRuB3DTpVw6ajzwSChwFw5omBMmMcWKiDcM`,
            }
        });
        console.log('mercure')
        return next(authReq)
    }
    //else if (req.url.startsWith(environment.genApiUrl)) {

    //     var jwt: JwtPayload = jwtDecode(genApiToken!)
    //     const tenantId = jwt.aud
    //     console.log(jwt.aud)
    //     const authReq = req.clone({
    //         setHeaders: {
    //             Authorization: `Bearer ${genApiToken}`,
    //             'Content-Type': 'application/x-www-form-urlencoded',
    //             'tenantId': String(tenantId)

    //         }
    //     });
    //     return next(authReq)
    // }
    else {
        console.log(environment.apiURL)
        console.log('requete interceptée')
        const authReq = req;
        return next(authReq);
    }
}
