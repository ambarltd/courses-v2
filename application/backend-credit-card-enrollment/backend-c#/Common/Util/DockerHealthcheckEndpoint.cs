using Microsoft.AspNetCore.Mvc;

namespace CreditCardEnrollment.Common.Util;

[ApiController]
public class DockerHealthcheckEndpoint : ControllerBase {
    [HttpGet("docker_healthcheck")]
    [HttpHead("docker_healthcheck")]
    [Produces("text/plain")] 
    public IActionResult RequestEnrollment() {
        return Ok("OK");
    }
}