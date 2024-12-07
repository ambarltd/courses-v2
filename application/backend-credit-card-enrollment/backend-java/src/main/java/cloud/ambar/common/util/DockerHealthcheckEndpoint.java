package cloud.ambar.common.util;

import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.*;

@RestController
public class DockerHealthcheckEndpoint {
    @GetMapping("/docker_healthcheck")
    @ResponseStatus(HttpStatus.OK)
    public String requestEnrollment() {
        return "OK";
    }
}

