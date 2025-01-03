using Microsoft.AspNetCore.Mvc;

namespace CreditCardEnrollment.Common.Util;

[ApiController]
public class DockerHealthcheckEndpoint : ControllerBase {
    [HttpGet("docker_healthcheck")]
    [Produces("text/plain")] 
    public string RequestEnrollment() {
        Response.StatusCode = 200;
        return "OK";
    }
}