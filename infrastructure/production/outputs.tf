output "all_outputs" {
  value = {
    pgt_proxy_ip: module.pgt_proxy.public_ip
  }
}