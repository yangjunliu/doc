# 1. chat
### 数据结构设计
长链接句柄
```golang
// 类型
1 服务器
2 联盟或者频道
3 工会
4 队伍
5 私聊

// 类型信息
struct ChatType {
  typeID int
  chatID int
}

// 句柄信息
struct UserSocket {
  f *socket
  chatType []ChatType
}
```
